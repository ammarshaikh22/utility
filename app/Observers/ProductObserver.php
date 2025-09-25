<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class ProductObserver
{
    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    public function saving(Product $product)
    {
        // Ensure correct unit type is set
        $this->unitType($product);

        // Track who last updated the product
        if (!isRunningInConsoleOrSeeding()) {
            $product->last_updated_by = user() ? user()->id : null;
        }
    }

    public function created(Product $product)
    {
        // Log employee activity after product creation
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');
        }
    }

    public function creating(Product $product)
    {
        // Track who created the product
        if (!isRunningInConsoleOrSeeding()) {
            $product->added_by = user() ? user()->id : null;
        }

        // Assign product to the current company
        if (company()) {
            $product->company_id = company()->id;
        }
    }

    public function updated(Product $product)
    {
        // Log employee activity after product update
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-updated', $product->id, 'product');
        }
    }

    public function deleted(Product $product)
    {
        // Log employee activity after product deletion
        if (user()) {
            self::createEmployeeActivity(user()->id, 'product-deleted');
        }
    }

    public function deleting(Product $product)
    {
        // Delete all associated files before removing the product
        $product->files()->each(function ($file) {
            $file->delete();
        });
    }
}
