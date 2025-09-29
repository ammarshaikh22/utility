<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Models\Project;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProjectActivity;
use App\Traits\EmployeeActivityTrait;
use App\Traits\ExcelImportable;

class ImportProductJob implements ShouldQueue
{
    // Traits for queue handling, serialization, logging, and reusable helpers
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait, EmployeeActivityTrait;
    use ExcelImportable;

    private $row;      // Single row of data from the import file
    private $columns;  // Column mapping from the import file
    private $company;  // Current company context (optional)

    /**
     * Initialize the job with import row, column mapping, and optional company
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job to import a single product.
     *
     * @return void
     */
    public function handle()
    {
        // Validate mandatory columns: product_name and price
        if ($this->isColumnExists('product_name') && $this->isColumnExists('price')) {

            // Clean up price by removing unwanted characters
            $cleanedPrice = preg_replace('/[^\d.]/', '', $this->getColumnValue('price'));

            // Ensure price is numeric
            if (!is_numeric($cleanedPrice)) {
                $this->failJob(__('messages.invalidData'));
                return;
            }

            DB::beginTransaction();
            try {
                // Create and populate Product instance
                $product = new Product();
                $product->company_id = $this->company?->id;
                $product->name = $this->getColumnValue('product_name');
                $product->price = $cleanedPrice;
                $product->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;
                $product->sku = $this->isColumnExists('sku') ? $this->getColumnValue('sku') : null;
                $product->allow_purchase = true;

                /**
                 * Assign Unit Type
                 * - If unit_type column exists, try to match from DB
                 * - Otherwise, fall back to default unit type
                 */
                if ($this->isColumnExists('unit_type')) {
                    $unitTypeName = $this->getColumnValue('unit_type');
                    $unitType = DB::table('unit_types')->where('unit_type', $unitTypeName)->first();

                    if ($unitType) {
                        $product->unit_id = $unitType->id;
                    } else {
                        $defaultUnitType = DB::table('unit_types')->where('default', true)->first();
                        $product->unit_id = $defaultUnitType ? $defaultUnitType->id : null;
                    }
                } else {
                    $defaultUnitType = DB::table('unit_types')->where('default', true)->first();
                    $product->unit_id = $defaultUnitType ? $defaultUnitType->id : null;
                }

                /**
                 * Assign Product Category
                 */
                if ($this->isColumnExists('product_category')) {
                    $categoryName = $this->getColumnValue('product_category');
                    $category = DB::table('product_category')->where('category_name', $categoryName)->first();
                    $product->category_id = $category ? $category->id : null;
                } else {
                    $product->category_id = null;
                }

                /**
                 * Assign Product Sub-category
                 * - Only assign if it belongs to the selected category
                 */
                if ($this->isColumnExists('product_sub_category')) {
                    $subCategoryName = $this->getColumnValue('product_sub_category');
                    $subCategory = DB::table('product_sub_category')->where('category_name', $subCategoryName)->first();

                    if ($subCategory) {
                        if ($subCategory->category_id == $product->category_id) {
                            $product->sub_category_id = $subCategory->id;
                        } else {
                            // Sub-category doesn’t belong to category → reset
                            $product->sub_category_id = null;
                        }
                    } else {
                        $product->sub_category_id = null;
                    }
                } else {
                    $product->sub_category_id = null;
                }

                // Track which user added the product
                $product->added_by = user() ? user()->id : null;

                // Save product
                $product->save();

                // Log employee activity for auditing
                self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');

                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('messages.invalidData'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }

        } else {
            // Mandatory columns missing → fail the job
            $this->failJob(__('messages.invalidData'));
        }
    }
}
