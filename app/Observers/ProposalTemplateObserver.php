<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\ProposalTemplateItemImage;
use App\Models\ProposalTemplate;
use App\Models\ProposalTemplateItem;
use App\Traits\UnitTypeSaveTrait;

class ProposalTemplateObserver
{
    use UnitTypeSaveTrait;

    /**
     * Triggered when a new ProposalTemplate is being created.
     * Automatically assigns the company_id from the current logged-in company.
     */
    public function creating(ProposalTemplate $proposal)
    {
        if (company()) {
            $proposal->company_id = company()->id;
        }
    }

    /**
     * Triggered after a ProposalTemplate is created.
     * Handles insertion of related items and their images from the request.
     */
    public function created(ProposalTemplate $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Check if request contains item data
            if (!empty(request()->item_name)) {
                $itemsSummary = request()->item_summary;
                $cost_per_item = request()->cost_per_item;
                $hsn_sac_code = request()->hsn_sac_code;
                $quantity = request()->quantity;
                $unitId = request()->unit_id;
                $product = request()->product_id;
                $amount = request()->amount;
                $tax = request()->taxes;
                $invoice_item_image = request()->invoice_item_image;
                $invoice_item_image_url = request()->invoice_item_image_url;

                foreach (request()->item_name as $key => $item) {
                    if (!is_null($item)) {
                        // Create proposal item for each request item
                        $proposalTemplateItem = ProposalTemplateItem::create([
                            'proposal_template_id' => $proposal->id,
                            'company_id' => $proposal->company_id,
                            'item_name' => $item,
                            'item_summary' => $itemsSummary[$key],
                            'type' => 'item',
                            'unit_id' => (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null,
                            'product_id' => (isset($product[$key]) && !is_null($product[$key])) ? $product[$key] : null,
                            'hsn_sac_code' => (isset($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                            'quantity' => $quantity[$key],
                            'unit_price' => round($cost_per_item[$key], 2),
                            'amount' => round($amount[$key], 2),
                            'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null),
                        ]);
                    }

                    // Handle associated invoice item images
                    if (isset($proposalTemplateItem) && (isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key]))) {
                        $proposalTemplateItemImage = new ProposalTemplateItemImage();
                        $proposalTemplateItemImage->proposal_template_item_id = $proposalTemplateItem->id;
                        $proposalTemplateItemImage->company_id = $proposalTemplateItem->company_id;

                        // If image uploaded, save file locally or to S3
                        if (isset($invoice_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3(
                                $invoice_item_image[$key],
                                ProposalTemplateItemImage::FILE_PATH . '/' . $proposalTemplateItem->id . '/'
                            );

                            $proposalTemplateItemImage->filename = $invoice_item_image[$key]->getClientOriginalName();
                            $proposalTemplateItemImage->hashname = $filename;
                            $proposalTemplateItemImage->size = $invoice_item_image[$key]->getSize();
                        }

                        // If external image link provided, set it
                        $proposalTemplateItemImage->external_link = isset($invoice_item_image[$key]) 
                            ? null 
                            : ($invoice_item_image_url[$key] ?? null);

                        $proposalTemplateItemImage->save();
                    }
                }
            }
        }
    }

    /**
     * Triggered when a ProposalTemplate is updated.
     * Handles synchronization of items and their images with the request.
     *
     * Process:
     * Step 1 - Delete items not present in the request
     * Step 2 - Update existing items, update images if changed
     * Step 3 - Insert new items with images
     */
    public function updated(ProposalTemplate $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $request = request();

            $items = $request->item_name;
            $itemsSummary = $request->item_summary;
            $hsn_sac_code = $request->hsn_sac_code;
            $tax = $request->taxes;
            $quantity = $request->quantity;
            $cost_per_item = $request->cost_per_item;
            $amount = $request->amount;
            $proposal_item_image = $request->invoice_item_image;
            $proposal_item_image_url = $request->invoice_item_image_url;
            $item_ids = $request->item_ids;
            $unitId = $request->unit_id;
            $productId = $request->product_id;

            if (!empty($items) && is_array($items)) {
                // Step 1: Delete removed items
                if (!empty($item_ids)) {
                    ProposalTemplateItem::whereNotIn('id', $item_ids)
                        ->where('proposal_template_id', $proposal->id)
                        ->delete();
                }

                // Step 2 & 3: Update existing items or create new ones
                foreach ($items as $key => $item) {
                    $invoice_item_id = $item_ids[$key] ?? 0;

                    $proposalTemplateItem = ProposalTemplateItem::find($invoice_item_id) ?? new ProposalTemplateItem();

                    $proposalTemplateItem->proposal_template_id = $proposal->id;
                    $proposalTemplateItem->company_id = $proposal->company_id;
                    $proposalTemplateItem->item_name = $item;
                    $proposalTemplateItem->item_summary = $itemsSummary[$key];
                    $proposalTemplateItem->type = 'item';
                    $proposalTemplateItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                    $proposalTemplateItem->product_id = (isset($productId[$key]) && !is_null($productId[$key])) ? $productId[$key] : null;
                    $proposalTemplateItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                    $proposalTemplateItem->quantity = $quantity[$key];
                    $proposalTemplateItem->unit_price = round($cost_per_item[$key], 2);
                    $proposalTemplateItem->amount = round($amount[$key], 2);
                    $proposalTemplateItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                    $proposalTemplateItem->save();

                    // Handle item images
                    if ((isset($proposal_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($proposal_item_image_url[$key])) {
                        $proposalTemplateItemImage = ProposalTemplateItemImage::where('proposal_template_item_id', $proposalTemplateItem->id)->firstOrNew();

                        $proposalTemplateItemImage->proposal_template_item_id = $proposalTemplateItem->id;
                        $proposalTemplateItemImage->company_id = $proposalTemplateItem->company_id;

                        // Delete old image if a new one is uploaded (but keep product images)
                        if (!isset($proposal_item_image_url[$key]) && $proposalTemplateItem->proposalTemplateItemImage) {
                            Files::deleteFile(
                                $proposalTemplateItem->proposalTemplateItemImage->hashname,
                                ProposalTemplateItemImage::FILE_PATH . '/' . $proposalTemplateItem->id . '/'
                            );
                        }

                        // Save new uploaded file
                        if (isset($proposal_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3(
                                $proposal_item_image[$key],
                                ProposalTemplateItemImage::FILE_PATH . '/' . $proposalTemplateItem->id . '/'
                            );

                            $proposalTemplateItemImage->filename = $proposal_item_image[$key]->getClientOriginalName();
                            $proposalTemplateItemImage->hashname = $filename;
                            $proposalTemplateItemImage->size = $proposal_item_image[$key]->getSize();
                        }

                        // If external link provided, use it instead of file
                        $proposalTemplateItemImage->external_link = isset($proposal_item_image[$key]) ? null : ($proposal_item_image_url[$key] ?? null);
                        $proposalTemplateItemImage->save();
                    }
                }
            }
        }
    }
}
