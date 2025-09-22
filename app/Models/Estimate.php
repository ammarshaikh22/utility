<?php

namespace App\Models;

/**
 * Imports necessary classes, traits, and relationships for the Estimate model.
 */
use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Estimate
 *
 * @property int $id
 * @property int $client_id
 * @property string|null $estimate_number
 * @property \Illuminate\Support\Carbon $valid_till
 * @property float $sub_total
 * @property float $discount
 * @property string $discount_type
 * @property float $total
 * @property int|null $currency_id
 * @property string $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $send_status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $client
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read mixed $valid_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EstimateItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\AcceptEstimate|null $sign
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereEstimateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereValidTill($value)
 * @property string|null $hash
 * @property int|null $unit_id
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereHash($value)
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculateTax($value)
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDescription($value)
 * @property int|null $company_id
 * @property-read \App\Models\ClientDetails $clientdetails
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property string|null $ip_address
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUnitId($value)
 * @property string|null $original_estimate_number
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereOriginalEstimateNumber($value)
 * @mixin \Eloquent
 */
class Estimate extends BaseModel
{

    // Applies notification functionality, custom fields, and company traits to the model
    use Notifiable, CustomFieldsTrait, HasCompany;

    // Casts date fields to Carbon instances for easier manipulation
    protected $casts = [
        'valid_till' => 'datetime',
        'last_viewed' => 'datetime',
    ];
    
    // Appends computed attributes to the model
    protected $appends = ['total_amount', 'valid_date'];
    
    // Eager loads currency relationship by default
    protected $with = ['currency'];

    // Defines the custom field model class for this estimate model
    const CUSTOM_FIELD_MODEL = 'App\Models\Estimate';

    /**
     * Defines the one-to-many relationship with EstimateItem model.
     * 
     * @return HasMany Relationship to EstimateItem model
     */
    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id');
    }

    /**
     * Defines the belongs-to relationship with Project model, including soft-deleted records.
     * 
     * @return BelongsTo Relationship to Project model
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    /**
     * Defines the belongs-to relationship with Company model.
     * 
     * @return BelongsTo Relationship to Company model
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Defines the belongs-to relationship with User model (client).
     * 
     * @return BelongsTo Relationship to User model
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Defines the belongs-to relationship with ClientDetails model.
     * 
     * @return BelongsTo Relationship to ClientDetails model
     */
    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    /**
     * Defines the belongs-to relationship with Currency model.
     * 
     * @return BelongsTo Relationship to Currency model
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Defines the belongs-to relationship with UnitType model.
     * 
     * @return BelongsTo Relationship to UnitType model
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    /**
     * Defines the one-to-one relationship with AcceptEstimate model.
     * 
     * @return HasOne Relationship to AcceptEstimate model
     */
    public function sign(): HasOne
    {
        return $this->hasOne(AcceptEstimate::class, 'estimate_id');
    }

    /**
     * Accessor for formatted total amount with currency symbol.
     * 
     * @return string Formatted total amount or empty string
     */
    public function getTotalAmountAttribute()
    {
        return (!is_null($this->total) && isset($this->currency) && !is_null($this->currency->currency_symbol)) ? $this->currency->currency_symbol . $this->total : '';
    }

    /**
     * Accessor for formatted valid date in 'd F, Y' format.
     * 
     * @return string Formatted date or empty string
     */
    public function getValidDateAttribute()
    {
        return !is_null($this->valid_till) ? Carbon::parse($this->valid_till)->format('d F, Y') : '';
    }

    /**
     * Formats the estimate number according to company invoice settings.
     * 
     * @return string Formatted estimate number
     */
    public function formatEstimateNumber()
    {
        $invoiceSettings = (company()) ? company()->invoiceSetting : $this->company->invoiceSetting;
        return \App\Helper\NumberFormat::estimate($this->estimate_number, $invoiceSettings);
    }

    /**
     * Retrieves the last estimate number from the database.
     * 
     * @return int Last estimate number or 0
     */
    public static function lastEstimateNumber()
    {
        return (int)Estimate::orderBy('id', 'desc')->first()?->original_estimate_number ?? 0;
    }

    /**
     * Defines the belongs-to relationship with EstimateRequest model.
     * 
     * @return BelongsTo Relationship to EstimateRequest model
     */
    public function estimateRequest(): BelongsTo
    {
        return $this->belongsTo(EstimateRequest::class, 'estimate_request_id');
    }

}