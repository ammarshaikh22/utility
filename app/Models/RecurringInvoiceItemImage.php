<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\RecurringInvoiceItemImage
 *
 * Represents an image associated with a recurring invoice item.
 *
 * @property int $id Primary key.
 * @property int $invoice_recurring_item_id ID of the recurring invoice item this image belongs to.
 * @property string $filename Original file name uploaded.
 * @property string|null $hashname Internal hash name used for storage.
 * @property string|null $image Optional image path.
 * @property string|null $size File size.
 * @property string|null $external_link External URL if file is hosted elsewhere.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when record was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when record was last updated.
 * @property-read mixed $file_url Full URL to access the file.
 * @property-read mixed $icon Icon attribute from IconTrait.
 * @property-read mixed $file File path or external link.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereInvoiceRecurringItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RecurringInvoiceItemImage extends BaseModel
{
    use IconTrait;

    // Base directory for storing recurring invoice files
    const FILE_PATH = 'recurring-invoice-files';

    // Database table name
    protected $table = 'invoice_recurring_item_images';

    // Attributes to append automatically when serializing
    protected $appends = ['file_url', 'icon', 'file'];

    // Mass assignable attributes
    protected $fillable = ['invoice_recurring_item_id', 'filename', 'hashname', 'size', 'external_link'];

    /**
     * Get the full URL to the file.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        if ($this->external_link) {
            // If external link exists, return it. Ensure full URL.
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        // Build local storage URL
        return asset_url_local_s3(
            RecurringInvoiceItemImage::FILE_PATH . '/' . $this->invoice_recurring_item_id . '/' . $this->hashname
        );
    }

    /**
     * Get file path or external link.
     *
     * @return string
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (RecurringInvoiceItemImage::FILE_PATH . '/' . $this->invoice_recurring_item_id . '/' . $this->hashname);
    }
}
