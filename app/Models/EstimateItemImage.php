<?php

namespace App\Models;

/**
 * Imports necessary trait and relationship class for EstimateItemImage model.
 */
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EstimateItemImage
 *
 * @property int $id
 * @property int $estimate_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereEstimateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereUpdatedAt($value)
 * @property-read \App\Models\EstimateItem $item
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class EstimateItemImage extends BaseModel
{

    // Applies icon generation functionality to the model
    use IconTrait;

    // Defines the storage path for estimate files
    const FILE_PATH = 'estimate-files';

    // Appends computed attributes for file URL, icon, and file path
    protected $appends = ['file_url', 'icon', 'file'];
    
    // Defines mass assignable fields for the model
    protected $fillable = ['estimate_item_id', 'filename', 'hashname', 'size', 'external_link'];

    /**
     * Accessor to generate the full URL for the estimate item image file.
     * Handles both external links and local S3 storage paths.
     * 
     * @return string Complete file URL
     */
    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(EstimateItemImage::FILE_PATH . '/' . $this->estimate_item_id . '/' . $this->hashname);
    }

    /**
     * Accessor to get the file path, preferring external link if available.
     * 
     * @return string File path or external link
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (EstimateItemImage::FILE_PATH . '/' . $this->estimate_item_id . '/' . $this->hashname);
    }

    /**
     * Defines the belongs-to relationship with EstimateItem model.
     * 
     * @return BelongsTo Relationship to EstimateItem model
     */
    public function item() : BelongsTo
    {
        return $this->belongsTo(EstimateItem::class, 'estimate_item_id');
    }

}