<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\CustomField\StoreCustomField;
use App\Http\Requests\CustomField\UpdateCustomField;

class CustomFieldController extends AccountBaseController
{
    /**
     * Constructor for the CustomFieldController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to users with full custom field management permissions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customFields';
        $this->activeSettingMenu = 'custom_fields';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user does not have 'all' permission to manage custom field settings
            abort_403(user()->permission('manage_custom_field_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * Displays the custom fields index page.
     * Joins custom fields with their groups, selects relevant columns, groups by module, and renders the index view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Join custom fields with groups and select relevant columns
        $this->customFields = CustomField::join('custom_field_groups', 'custom_field_groups.id', '=', 'custom_fields.custom_field_group_id')
                ->select('custom_fields.id', 'custom_field_groups.name as module', 'custom_fields.label', 'custom_fields.type', 'custom_fields.values', 'custom_fields.required', 'custom_fields.export', 'custom_fields.visible')
                ->get();

        // Group custom fields by module
        $this->groupedCustomFields = $this->customFields->groupBy('module');

        // Render the custom fields index view
        return view('custom-fields.index', $this->data);
    }

    /**
     * Displays the form for creating a new custom field.
     * Retrieves all custom field groups and available field types, and renders the create modal view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Fetch all custom field groups and available field types
        $this->customFieldGroups = CustomFieldGroup::all();
        $this->types = ['text', 'number', 'password', 'textarea', 'select', 'radio', 'date', 'checkbox', 'file'];

        // Render the create custom field modal view
        return view('custom-fields.create-custom-field-modal', $this->data);
    }

    /**
     * Stores a new custom field.
     * Validates the input using the StoreCustomField request, generates a unique slug, prepares field data, calls the addCustomField helper, and returns a success response.
     *
     * @param StoreCustomField $request The validated request containing custom field data.
     * @return array JSON response with success message.
     */
    public function store(StoreCustomField $request)
    {
        // Generate a unique slug for the field name
        $name = CustomField::generateUniqueSlug($request->get('label'), $request->module);

        // Prepare field data for the group
        $group = [
            'fields' => [
                [
                    'name' => $name,
                    'custom_field_group_id' => $request->module,
                    'label' => $request->get('label'),
                    'type' => $request->get('type'),
                    'required' => $request->get('required'),
                    'values' => $request->get('value'),
                    'export' => $request->get('export'),
                    'visible' => $request->get('visible'),
                ]
            ],
        ];

        // Add the custom field using the helper method
        $this->addCustomField($group);

        // Return success response
        return Reply::success('messages.recordSaved');
    }

    /**
     * Displays the form for editing an existing custom field.
     * Retrieves the specified custom field, decodes its values, and renders the edit modal view.
     *
     * @param int $id The ID of the custom field to edit.
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the custom field and decode its values
        $this->field = CustomField::findOrFail($id);
        $this->field->values = json_decode($this->field->values);

        // Render the edit custom field modal view
        return view('custom-fields.edit-custom-field-modal', $this->data);
    }

    /**
     * Updates an existing custom field.
     * Validates the input using the UpdateCustomField request, generates a unique slug, updates the field, and returns a success response.
     *
     * @param UpdateCustomField $request The validated request containing updated custom field data.
     * @param int $id The ID of the custom field to update.
     * @return array JSON response with success message.
     */
    public function update(UpdateCustomField $request, $id)
    {
        // Fetch the custom field
        $field = CustomField::findOrFail($id);

        // Generate a unique slug for the field name
        $name = CustomField::generateUniqueSlug($request->label, $field->custom_field_group_id);

        // Update the field properties
        $field->label = $request->label;
        $field->name = $name;
        $field->values = json_encode($request->value);
        $field->required = $request->required;
        $field->export = $request->export;
        $field->visible = $request->visible;
        $field->save();

        // Return success response
        return Reply::success('messages.updateSuccess');
    }

    /**
     * Deletes a custom field.
     * Validates the input, removes the specified custom field, fetches the updated count for the module, and returns a success response with the count.
     *
     * @param int $id The ID of the custom field to delete.
     * @return array JSON response with success message and updated module count.
     */
    public function destroy($id)
    {
        // Fetch the custom field and its module
        $field = CustomField::findOrFail($id);
        $module = $field->fieldGroup->name;

        // Delete the custom field
        $field->delete();

        // Fetch the updated count of custom fields for the module
        $updatedCount = CustomField::whereHas('fieldGroup', function ($query) use ($module) {
            $query->where('name', $module);
        })->count();

        // Return success response with the updated count
        return Reply::successWithData(__('messages.deleteSuccess'), ['updatedCount' => $updatedCount]);
    }

    /**
     * Helper method to add custom fields from a group data structure.
     * Processes field data, handles required status and values, and creates the custom field records.
     *
     * @param array $group The group data containing fields to add.
     */
    private function addCustomField($group)
    {
        // Add custom fields for this group
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => $field['custom_field_group_id'],
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type'],
                'export' => $field['export'],
                'visible' => $field['visible']
            ];

            // Set required status
            if (isset($field['required']) && (in_array($field['required'], ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            } else {
                $insertData['required'] = 'no';
            }

            // Handle values (single value as text, multi-value as JSON)
            if (isset($field['values'])) {
                if (is_array($field['values'])) {
                    $insertData['values'] = \GuzzleHttp\json_encode($field['values']);
                } else {
                    $insertData['values'] = $field['values'];
                }
            }

            // Create the custom field record
            CustomField::create($insertData);
        }
    }
}