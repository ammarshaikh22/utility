<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\Proposal;
use App\Models\Notification;
use App\Models\ProposalItem;
use App\Events\NewProposalEvent;
use App\Models\ProposalItemImage;
use App\Traits\UnitTypeSaveTrait;
use App\Models\ProposalTemplateItemImage;
use App\Traits\DealHistoryTrait;
use App\Traits\EmployeeActivityTrait;

class ProposalObserver
{
    use EmployeeActivityTrait;
    use UnitTypeSaveTrait, DealHistoryTrait;

    /**
     * Triggered before saving a Proposal.
     * Updates audit fields and tax calculation flag.
     */
    public function saving(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $proposal->last_updated_by = user()->id;
        }

        if (request()->has('calculate_tax')) {
            $proposal->calculate_tax = request()->calculate_tax;
        }
    }

    /**
     * Triggered before creating a new Proposal.
     * Assigns unique hash, user, company, and send status.
     */
    public function creating(Proposal $proposal)
    {
        $proposal->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding() && user()) {
            $proposal->added_by = user()->id;
        }

        if (company()) {
            $proposal->company_id = company()->id;
        }

        // Mark as "send" based on request type
        $proposal->send_status = (
            (request()->type && request()->type == 'send') || request()->type == 'mark_as_send'
        ) ? 1 : 0;
    }

    /**
     * Triggered after a Proposal is created.
     * Handles deal history, employee activity, items creation, and notifications.
     */
    public function created(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Record deal history
            self::createDealHistory($proposal->deal_id, 'proposal-created', proposalId: $proposal->id);

            // Record employee activity
            if (user()) {
                self::createEmployeeActivity(user()->id, 'proposal-created', $proposal->id, 'proposal');
            }

            // Create proposal items and handle images
            if (!empty(request()->item_name)) {
                $this->createProposalItemsWithImages($proposal);
            }

            // Trigger "new proposal" event if sending
            if (request()->type == 'send') {
                event(new NewProposalEvent($proposal, 'new'));
            }
        }
    }

    /**
     * Triggered before updating a Proposal.
     * Sets send status when type is "send" or "mark_as_send".
     */
    public function updating(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ((request()->type && request()->type == 'send') || request()->type == 'mark_as_send') {
                $proposal->send_status = 1;
            }
        }
    }

    /**
     * Triggered after a Proposal is updated.
     * Handles employee activity, status changes, and item updates with images.
     */
    public function updated(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Record employee activity
            if (user()) {
                self::createEmployeeActivity(user()->id, 'proposal-updated', $proposal->id, 'proposal');
            }

            // Send event if status changed to signed
            if ($proposal->isDirty('status')) {
                event(new NewProposalEvent($proposal, 'signed'));
            }

            // Update items and their images
            $this->updateProposalItemsWithImages($proposal);
        }
    }

    /**
     * Triggered after a Proposal is deleted.
     * Records history and employee activity.
     */
    public function deleted(Proposal $proposal)
    {
        if (user()) {
            self::createDealHistory($proposal->deal_id, 'proposal-deleted');
            self::createEmployeeActivity(user()->id, 'proposal-deleted');
        }
    }

    /**
     * Triggered before deleting a Proposal.
     * Removes related notifications.
     */
    public function deleting(Proposal $proposal)
    {
        $notifyData = ['App\Notifications\NewProposal', 'App\Notifications\ProposalSigned'];
        Notification::deleteNotification($notifyData, $proposal->id);
    }

    /**
     * Helper method: Creates Proposal items and stores associated images.
     */
    protected function createProposalItemsWithImages(Proposal $proposal)
    {
        $itemsSummary = request()->item_summary;
        $cost_per_item = request()->cost_per_item;
        $hsn_sac_code = request()->hsn_sac_code;
        $quantity = request()->quantity;
        $unitId = request()->unit_id;
        $product = request()->product_id;
        $amount = request()->amount;
        $tax = request()->taxes;
        $invoice_item_image = request()->invoice_item_image;
        $invoice_item_image_delete = request()->invoice_item_image_delete;
        $invoice_item_image_url = request()->invoice_item_image_url;
        $invoiceOldImage = request()->image_id;

        foreach (request()->item_name as $key => $item) {
            if (!is_null($item)) {
                // Create proposal item
                $proposalItem = ProposalItem::create([
                    'proposal_id' => $proposal->id,
                    'item_name' => $item,
                    'item_summary' => $itemsSummary[$key],
                    'type' => 'item',
                    'unit_id' => $unitId[$key] ?? null,
                    'product_id' => $product[$key] ?? null,
                    'hsn_sac_code' => $hsn_sac_code[$key] ?? null,
                    'quantity' => $quantity[$key],
                    'unit_price' => round($cost_per_item[$key], 2),
                    'amount' => round($amount[$key], 2),
                    'taxes' => $tax ? ($tax[$key] ?? null ? json_encode($tax[$key]) : null) : null,
                    'field_order' => $key + 1,
                ]);

                // Handle invoice images (upload or duplicate from template)
                $this->handleProposalItemImages(
                    $proposalItem,
                    $invoice_item_image[$key] ?? null,
                    $invoice_item_image_url[$key] ?? null,
                    $invoice_item_image_delete[$key] ?? null,
                    $invoiceOldImage[$key] ?? null
                );
            }
        }
    }

    /**
     * Helper method: Updates existing Proposal items and handles images.
     */
    protected function updateProposalItemsWithImages(Proposal $proposal)
    {
        $request = request();

        $items = $request->item_name;
        $itemsSummary = $request->item_summary;
        $hsn_sac_code = $request->hsn_sac_code;
        $tax = $request->taxes;
        $quantity = $request->quantity;
        $unitId = $request->unit_id;
        $productId = $request->product_id;
        $cost_per_item = $request->cost_per_item;
        $amount = $request->amount;
        $proposal_item_image = $request->invoice_item_image;
        $proposal_item_image_url = $request->invoice_item_image_url;
        $item_ids = $request->item_ids;

        if (!empty($items) && is_array($items)) {
            // Step1: Delete items not in request
            if (!empty($item_ids)) {
                ProposalItem::whereNotIn('id', $item_ids)
                    ->where('proposal_id', $proposal->id)
                    ->delete();
            }

            // Step2: Update or create items
            foreach ($items as $key => $item) {
                $proposalItem = ProposalItem::find($item_ids[$key] ?? 0) ?? new ProposalItem();

                $proposalItem->proposal_id = $proposal->id;
                $proposalItem->item_name = $item;
                $proposalItem->item_summary = $itemsSummary[$key];
                $proposalItem->type = 'item';
                $proposalItem->unit_id = $unitId[$key] ?? null;
                $proposalItem->product_id = $productId[$key] ?? null;
                $proposalItem->hsn_sac_code = $hsn_sac_code[$key] ?? null;
                $proposalItem->quantity = $quantity[$key];
                $proposalItem->unit_price = round($cost_per_item[$key], 2);
                $proposalItem->amount = round($amount[$key], 2);
                $proposalItem->taxes = $tax ? ($tax[$key] ?? null ? json_encode($tax[$key]) : null) : null;
                $proposalItem->field_order = $key + 1;
                $proposalItem->save();

                // Handle invoice item images
                if (
                    (isset($proposal_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) ||
                    isset($proposal_item_image_url[$key])
                ) {
                    $this->updateProposalItemImage($proposalItem, $proposal_item_image[$key] ?? null, $proposal_item_image_url[$key] ?? null);
                }
            }
        }
    }

    /**
     * Helper method: Handles creation of item images (upload / duplication).
     */
    protected function handleProposalItemImages($proposalItem, $uploadedFile, $url, $deleteFlag, $oldImageId)
    {
        $useImage = !isset($deleteFlag);

        if ($uploadedFile || $url) {
            $filename = '';

            if ($uploadedFile) {
                $filename = Files::uploadLocalOrS3($uploadedFile, ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
            }

            ProposalItemImage::create([
                'proposal_item_id' => $proposalItem->id,
                'filename' => $uploadedFile ? $uploadedFile->getClientOriginalName() : '',
                'hashname' => $uploadedFile ? $filename : '',
                'size' => $uploadedFile ? $uploadedFile->getSize() : '',
                'external_link' => $uploadedFile ? null : $url,
            ]);
        }

        if ($useImage && $oldImageId) {
            $estimateOldImg = ProposalTemplateItemImage::find($oldImageId);
            if ($estimateOldImg) {
                $this->duplicateImageStore($estimateOldImg, $proposalItem);
            }
        }
    }

    /**
     * Helper method: Updates item images for Proposal items.
     */
    protected function updateProposalItemImage($proposalItem, $uploadedFile, $url)
    {
        $filename = '';
        $size = null;

        // Delete old file if not external URL
        if (!$url && $proposalItem->proposalItemImage) {
            Files::deleteFile($proposalItem->proposalItemImage->hashname, ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
        }

        if ($uploadedFile) {
            $filename = Files::uploadLocalOrS3($uploadedFile, ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
            $size = $uploadedFile->getSize();
        }

        ProposalItemImage::updateOrCreate(
            ['proposal_item_id' => $proposalItem->id],
            [
                'filename' => $uploadedFile ? $uploadedFile->getClientOriginalName() : '',
                'hashname' => $uploadedFile ? $filename : '',
                'size' => $size ?? '',
                'external_link' => $uploadedFile ? null : ($url ?? ''),
            ]
        );
    }

    /**
     * Duplicates an image from a ProposalTemplateItemImage to a new ProposalItemImage.
     */
    public function duplicateImageStore($estimateOldImg, $proposalItem)
    {
        if ($estimateOldImg) {
            $file = new ProposalItemImage();
            $file->proposal_item_id = $proposalItem->id;

            $fileName = Files::generateNewFileName($estimateOldImg->filename);

            Files::copy(
                ProposalTemplateItemImage::FILE_PATH . '/' . $estimateOldImg->id . '/' . $estimateOldImg->hashname,
                ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/' . $fileName
            );

            $file->filename = $estimateOldImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateOldImg->size;
            $file->save();
        }
    }
}
