<?php

namespace App\Models;

/**
 * Imports necessary trait for EstimateTemplateItemImage model.
 */
use App\Traits\IconTrait;

/**
 * App\Models\EstimateTemplateItemImage
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $estimate_template_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereEstimateTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
class EstimateTemplateItemImage extends BaseModel
{
    // Applies icon generation functionality to the model
    use IconTrait;

    // Defines the storage path for estimate template files
    const FILE_PATH = 'estimate-files';

    // Appends computed attributes for file URL, icon, and file path
    protected $appends = ['file_url', 'icon', 'file'];
    
    // Defines mass assignable fields for the model
    protected $fillable = ['estimate_template_item_id', 'filename', 'hashname', 'size', 'external_link'];

    /**
     * Accessor to generate the full URL for the estimate template item image file.
     * Handles both external links and local S3 storage paths.
     * 
     * @return string Complete file URL
     */
    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(EstimateTemplateItemImage::FILE_PATH . '/' . $this->estimate_template_item_id . '/' . $this->hashname);
    }

    /**
     * Accessor to get the file path, preferring external link if available.
     * 
     * @return string File path or external link
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (EstimateTemplateItemImage::FILE_PATH . '/' . $this->estimate_template_item_id . '/' . $this->hashname);
    }

}