<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Helper\Files;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

/**
 * Trait CustomFieldsTrait
 *
 * Provides reusable methods for handling custom fields:
 * - Defining & updating custom fields
 * - Retrieving groups & field data
 * - Saving field values (including files & dates)
 * - Accessing extra custom field metadata
 */
trait CustomFieldsTrait
{
    /** @var ReflectionClass Holds reflection data of the current model */
    public $model;

    /** @var mixed Stores extra data (custom field groups & fields) */
    private $extraData;

    /** @var mixed Stores custom field definitions for the model */
    public $custom_fields;

    /** @var mixed Stores custom field values for the model */
    public $custom_fields_data;

    /**
     * Get the model name of the class using this trait.
     *
     * @return string Fully qualified class name.
     */
    private function getModelName()
    {
        $model = new ReflectionClass($this);
        $this->model = $model;

        return $this->model->getName();
    }

    /**
     * Insert or update custom fields for a given group definition.
     *
     * @param array $group Array of field definitions (label, name, type, required, value).
     */
    public function updateCustomField($group)
    {
        // Loop through each field and insert into DB
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => 1, // Hardcoded group ID
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type']
            ];

            // Required field flag
            $insertData['required'] = (isset($field['required']) && in_array(strtolower($field['required']), ['yes', 'on', 1]))
                ? 'yes' : 'no';

            // Handle default values (single vs multi-value)
            if (isset($field['value'])) {
                $insertData['values'] = is_array($field['value'])
                    ? json_encode($field['value'])
                    : $field['value'];
            }

            DB::table('custom_fields')->insert($insertData);
        }
    }

    /**
     * Fetch custom field groups for the current model.
     *
     * @param bool $fields If true, load related custom fields as well.
     * @return \App\Models\CustomFieldGroup|null
     */
    public function getCustomFieldGroups($fields = false)
    {
        $customFieldGroup = CustomFieldGroup::where('model', $this->getModelName());

        // If model has company scope, filter by company_id
        $customFieldGroup = $customFieldGroup->when(method_exists($this, 'company'), function ($query) {
            return $query->where('company_id', $this->company_id ?: company()->id);
        })->first();

        // Optionally load fields
        if ($fields && $customFieldGroup) {
            $customFieldGroup->load(['customField'])->append(['fields']);
        }

        return $customFieldGroup;
    }

    /**
     * Convenience method: Get custom field groups with fields preloaded.
     *
     * @return \App\Models\CustomFieldGroup|null
     */
    public function getCustomFieldGroupsWithFields()
    {
        return $this->getCustomFieldGroups(true);
    }

    /**
     * Retrieve all custom field values for the current model instance.
     *
     * @return \Illuminate\Support\Collection Key-value pairs of field_id => value
     */
    public function getCustomFieldsData()
    {
        $modelId = $this->id;

        /** @var \Illuminate\Database\Eloquent\Collection $data */
        $data = DB::table('custom_fields_data')
            ->rightJoin('custom_fields', function ($query) use ($modelId) {
                $query->on('custom_fields_data.custom_field_id', '=', 'custom_fields.id');
                $query->on('model_id', '=', DB::raw($modelId));
            })
            ->rightJoin('custom_field_groups', 'custom_fields.custom_field_group_id', '=', 'custom_field_groups.id')
            ->select(
                'custom_fields.id',
                DB::raw('CONCAT("field_", custom_fields.id) as field_id'),
                'custom_fields.type',
                'custom_fields_data.value'
            )
            ->where('custom_field_groups.model', $this->getModelName())
            ->get();

        // Transform into ['field_{id}' => value] array
        return collect($data)->pluck('value', 'field_id');
    }

    /**
     * Insert or update custom field values for the current model.
     *
     * @param array $fields Key-value array of field_id => value.
     * @param int|null $company_id Optional company ID.
     */
    public function updateCustomFieldData($fields, $company_id = null)
    {
        foreach ($fields as $key => $value) {
            // Extract numeric field ID from key
            $idarray = explode('_', $key);
            $id = end($idarray);

            $fieldType = CustomField::findOrFail($id)->type;
            $company = $company_id ? Company::findOrFail($company_id) : company();

            // Special handling for dates (convert to Y-m-d format)
            $value = ($fieldType == 'date')
                ? Carbon::createFromFormat(companyOrGlobalSetting()->date_format, $value)->format('Y-m-d')
                : $value;

            // Special handling for files (upload to local/S3)
            $value = ($fieldType == 'file' && !is_string($value) && !is_null($value))
                ? Files::uploadLocalOrS3($value, 'custom_fields')
                : $value;

            // Check if entry already exists
            $entry = DB::table('custom_fields_data')
                ->where('model', $this->getModelName())
                ->where('model_id', $this->id)
                ->where('custom_field_id', $id)
                ->first();

            if ($entry) {
                // If replacing a file, delete the old one
                if ($fieldType == 'file' && (!is_null($entry->value) && $entry->value != $value)) {
                    Files::deleteFile($entry->value, 'custom_fields');
                }

                // Update existing record
                DB::table('custom_fields_data')
                    ->where('model', $this->getModelName())
                    ->where('model_id', $this->id)
                    ->where('custom_field_id', $id)
                    ->update(['value' => $value]);
            } else {
                // Insert new record
                DB::table('custom_fields_data')->insert([
                    'model' => $this->getModelName(),
                    'model_id' => $this->id,
                    'custom_field_id' => $id,
                    'value' => (!is_null($value)) ? $value : ''
                ]);
            }
        }
    }

    /**
     * Accessor for "extras" attribute (custom field groups & fields).
     *
     * @return mixed
     */
    public function getExtrasAttribute()
    {
        if ($this->extraData == null) {
            $this->extraData = $this->getCustomFieldGroupsWithFields();
        }

        return $this->extraData;
    }

    /**
     * Load both custom fields and their data into the model instance.
     *
     * @return $this
     */
    public function withCustomFields()
    {
        $this->custom_fields = $this->getCustomFieldGroupsWithFields();
        $this->custom_fields_data = $this->getCustomFieldsData();

        return $this;
    }
}
