<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\Estimate;
use Illuminate\Support\Str;
use App\Models\EstimateItem;
use App\Models\Notification;
use App\Models\UniversalSearch;
use App\Events\NewEstimateEvent;
use App\Models\EstimateItemImage;
use App\Traits\UnitTypeSaveTrait;
use App\Events\EstimateAcceptedEvent;
use App\Events\EstimateDeclinedEvent;
use App\Models\EstimateTemplateItemImage;
use function user;
use App\Traits\EmployeeActivityTrait;

class EstimateObserver
{
    use EmployeeActivityTrait;
    use UnitTypeSaveTrait;

    /**
     * Handle the "saving" event for an Estimate.
     * Updates last_updated_by and handles tax calculation flag.
     */
    public function saving(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $estimate->last_updated_by = user()->id;
            }

            if (request()->has('calculate_tax')) {
                $estimate->calculate_tax = request()->calculate_tax;
            }
        }
    }

    /**
     * Handle the "creating" event for an Estimate.
     * Sets added_by, company_id, status, and generates estimate numbers.
     */
    public function creating(Estimate $estimate)
    {
        $estimate->hash = md5(microtime());

        if (user()) {
            $estimate->added_by = user()->id;
        }

        if (request()->type && (request()->type == 'save' || request()->type == 'draft')) {
            $estimate->send_status = 0;
        }

        if (request()->type == 'draft') {
            $estimate->status = 'draft';
        }

        if (company()) {
            $estimate->company_id = company()->id;
        }

        if (is_numeric($estimate->estimate_number)) {
            $estimate->estimate_number = $estimate->formatEstimateNumber();
        }

        $invoiceSettings = (company()) ? company()->invoiceSetting : $estimate->company->invoiceSetting;
        $estimate->original_estimate_number = str($estimate->estimate_number)->replace($invoiceSettings->estimate_prefix . $invoiceSettings->estimate_number_separator, '');
    }

    /**
     * Handle the "created" event for an Estimate.
     * Creates associated EstimateItems and handles images, firing events as needed.
     */
    public function created(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'estimate-created', $estimate->id, 'estimate');
            }

            // Handle Estimate Items
            if (!empty(request()->item_name)) {
                // ...processing items, images, and template images...
            }

            // Fire NewEstimateEvent if not saving as draft
            if (request()->type != 'save' && request()->type != 'draft') {
                event(new NewEstimateEvent($estimate));
            }
        }
    }

    /**
     * Handle the "updated" event for an Estimate.
     * Creates employee activity and fires accepted/declined events.
     */
    public function updated(Estimate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'estimate-updated', $estimate->id, 'estimate');
            }

            if ($estimate->status == 'declined') {
                event(new EstimateDeclinedEvent($estimate));
            } elseif ($estimate->status == 'accepted') {
                event(new EstimateAcceptedEvent($estimate));
            }
        }
    }

    /**
     * Handle the "deleting" event for an Estimate.
     * Cleans up universal search entries and notifications.
     */
    public function deleting(Estimate $estimate)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $estimate->id)
            ->where('module_type', 'estimate')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\NewEstimate'];
        Notification::deleteNotification($notifyData, $estimate->id);
    }

    /**
     * Handle the "deleted" event for an Estimate.
     * Logs employee activity after deletion.
     */
    public function deleted(Estimate $estimate)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'estimate-deleted');
        }
    }

    /**
     * Duplicates an old EstimateItemImage to a new EstimateItem.
     */
    public function duplicateImageStore($estimateOldImg, $estimateItem)
    {
        if (!is_null($estimateOldImg)) {
            $file = new EstimateItemImage();
            $file->estimate_item_id = $estimateItem->id;
            $fileName = Files::generateNewFileName($estimateOldImg->filename);

            Files::copy(
                EstimateItemImage::FILE_PATH . '/' . $estimateOldImg->item->id . '/' . $estimateOldImg->hashname,
                EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/' . $fileName
            );

            $file->filename = $estimateOldImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateOldImg->size;
            $file->save();
        }
    }

    /**
     * Duplicates an old template image to a new EstimateItem.
     */
    public function duplicateTemplateImageStore($estimateTemplateImg, $estimateItem)
    {
        if (!is_null($estimateTemplateImg)) {
            $file = new EstimateItemImage();
            $file->estimate_item_id = $estimateItem->id;
            $fileName = Files::generateNewFileName($estimateTemplateImg->filename);

            Files::copy(
                EstimateTemplateItemImage::FILE_PATH . '/' . $estimateTemplateImg->estimate_template_item_id . '/' . $estimateTemplateImg->hashname,
                EstimateItemImage::FILE_PATH . '/' . $estimateItem->id . '/' . $fileName
            );

            $file->filename = $estimateTemplateImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateTemplateImg->size;
            $file->save();
        }
    }
}
