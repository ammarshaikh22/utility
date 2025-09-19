<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $filename
 * @property string $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $doc_url
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereUserId($value)
 * @property-read \App\Models\User|null $client
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereCompanyId($value)
 * @mixin \Eloquent
 */
class ClientDocument extends BaseModel
{
    // Traits for icon generation and company-related functionality
    use IconTrait, HasCompany;

    // Constant defining the file storage path for client documents
    const FILE_PATH = 'client-docs';

    // Fields that can be mass assigned (appears to be empty - should be populated)
    protected $fillable = [];

    // Prevent mass assignment of ID
    protected $guarded = ['id'];

    // Specify the custom table name for this model
    protected $table = 'client_docs';

    // Append doc_url and icon attributes to model
    protected $appends = ['doc_url', 'icon'];

    /**
     * Define relationship with the User model (client)
     * Note: Method name suggests 'client' relationship but uses 'user_id' foreign key
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Accessor for doc_url attribute - generates full URL to client document
     * Uses user_id in path structure for organization
     *
     * @return string
     */
    public function getDocUrlAttribute()
    {
        return asset_url_local_s3(ClientDocument::FILE_PATH . '/' . $this->user_id . '/' . $this->hashname);
    }

}