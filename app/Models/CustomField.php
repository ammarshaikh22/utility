<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\CustomField
 *
 * @property int $id
 * @property int|null $custom_field_group_id
 * @property string $label
 * @property string $name
 * @property bool $export
 * @property string $type
 * @property string $required
 * @property string|null $values
 * @property string|null $visible
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCustomFieldGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereValues($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\LeadCustomForm|null $leadCustomForm
 * @property-read \App\Models\TicketCustomForm|null $ticketCustomForm
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereExport($value)
 * @property-read \App\Models\CustomFieldGroup|null $customFieldGroup
 * @property-read \App\Models\CustomFieldGroup|null $fieldGroup
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereVisible($value)
 * @mixin \Eloquent
 */
class CustomField extends BaseModel
{

    use HasCompany;

    /**
     * This model does not use timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable (except ID)
     */
    protected $guarded = ['id'];

    /**
     * Relationship: CustomField has one LeadCustomForm
     */
    public function leadCustomForm(): HasOne
    {
        return $this->hasOne(LeadCustomForm::class, 'custom_fields_id');
    }

    /**
     * Relationship: CustomField has one TicketCustomForm
     */
    public function ticketCustomForm(): HasOne
    {
        return $this->hasOne(TicketCustomForm::class, 'custom_fields_id');
    }

    /**
     * Relationship: CustomField has one CustomFieldGroup
     */
    public function customFieldGroup(): HasOne
    {
        return $this->hasOne(CustomFieldGroup::class, 'custom_field_group_id');
    }

    /**
     * Relationship: CustomField belongs to one CustomFieldGroup
     */
    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(CustomFieldGroup::class, 'custom_field_group_id');
    }

    /**
     * Static method: Get custom fields for export based on model and group
     * @param string $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function exportCustomFields($model)
    {
        $customFieldsGroupsId = CustomFieldGroup::where('model', $model::CUSTOM_FIELD_MODEL)->select('id')->first();
        $customFields = collect();

        if ($customFieldsGroupsId) {
            $customFields = CustomField::where('custom_field_group_id', $customFieldsGroupsId->id)->where(function ($q) {
                return $q->where('export', 1)->orWhere('visible', 'true');
            })->get();
        }

        return $customFields;
    }

    /**
     * Static method: Generate custom field data for datatables display
     * @param object $datatables
     * @param string $model
     * @param string|null $relation
     * @return array
     */
    public static function customFieldData($datatables, $model, $relation = null)
    {
        $customFields = CustomField::exportCustomFields($model);
        $customFieldNames = [];
        $customFieldsId = $customFields->pluck('id');

        $fieldData = DB::table('custom_fields_data')->where('model', $model)->whereIn('custom_field_id', $customFieldsId)->select('id', 'custom_field_id', 'model_id', 'value')->get();

        foreach ($customFields as $customField) {
            $datatables->addColumn($customField->name, function ($row) use ($fieldData, $customField, $relation) {

                $finalData = $fieldData->filter(function ($value) use ($customField, $row, $relation) {
                    return ($value->custom_field_id == $customField->id) && ($value->model_id == ($relation ? $row?->{$relation}?->id : $row->id));
                })->first();

                if ($customField->type == 'select') {
                    $data = $customField->values;
                    $data = json_decode($data); // string to array

                    return $finalData ? (($finalData->value >= 0 && $finalData->value != null) ? $data[$finalData->value] : '--') : '--';
                }

                if ($customField->type == 'date') {
                    $dateValue = $finalData?->value;
                    if (!empty($dateValue)) {
                        try {
                            $formattedDate = \Carbon\Carbon::parse($dateValue)->translatedFormat(company()->date_format);
                            return $formattedDate;
                        } catch (\Exception $e) {
                            return '<span class="text-danger">' . __('Invalid Date') . '</span>';
                        }
                    }
                    return '--';
                }

                if ($customField->type == 'file') {
                    return $finalData ? '<a href="' . asset_url_local_s3('custom_fields/' . $finalData->value) . '" target="__blank" class="text-dark-grey">' . __('app.storageSetting.viewFile') . '</a>' : '--';
                }

                return $finalData ? $finalData->value : '--';
            });

            // This will use for datatable raw column
            if ($customField->type == 'file') {
                $customFieldNames[] = $customField->name;
            }

        }

        return $customFieldNames;
    }

    /**
     * Static method: Generate unique slug for custom field name
     * @param string $label
     * @param int $moduleId
     * @return string
     */
    public static function generateUniqueSlug($label, $moduleId)
    {
        $slug = str_slug($label);
        $count = CustomField::where('name', $slug)->where('custom_field_group_id', $moduleId)->count();

        if ($count > 0) {
            $i = 1;

            while (CustomField::where('name', $slug . '-' . $i)->where('custom_field_group_id', $moduleId)->count() > 0) {
                $i++;
            }

            $slug .= '-' . $i;
        }

        return $slug;

    }

}