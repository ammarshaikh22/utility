<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceFiles
 *
 * Model representing files attached to invoices.
 * Handles file storage, metadata, and relationships to invoices.
 *
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles query()
 * @property int $id
 * @property int $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvoiceFiles extends BaseModel
{
    // Trait that provides icon handling functionality
    use IconTrait;

    // Directory path where invoice files are stored
    const FILE_PATH = 'invoices';

    // Mass-assignable fields (none allowed here for security)
    protected $fillable = [];

    // Guarded fields (id is protected from mass assignment)
    protected $guarded = ['id'];

    // Database table associated with this model
    protected $table = 'invoice_files';

    // Dates that should be mutated to Carbon instances
    public $dates = ['created_at', 'updated_at'];

    // Accessor attributes automatically appended to model
    protected $appends = ['file_url', 'icon'];

    // Disabling Laravel's automatic timestamps
    public $timestamps = false;

    /**
     * Accessor for file URL
     * Generates the full S3/local path to the stored invoice file
     */
    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(InvoiceFiles::FILE_PATH . '/' . $this->hashname);
    }

    /**
     * Relationship: Each file belongs to a single invoice
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
