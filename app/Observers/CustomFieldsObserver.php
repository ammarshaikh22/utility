<?php

namespace App\Observers;

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\LeadCustomForm;
use App\Models\TicketCustomForm;
use Illuminate\Support\Facades\DB;

class CustomFieldsObserver
{
    /**
     * Handle the "creating" event.
     *
     * Automatically assigns the company_id when a new custom field is created,
     * ensuring that the field belongs to the active company context.
     */
    public function creating(CustomField $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * After a custom field is created, check if it belongs to either the
     * "Lead" group or the "Ticket" group. If so, automatically create
     * corresponding form entries for that group.
     */
    public function created(CustomField $customField)
    {
        $this->lead($customField);
        $this->ticket($customField);
    }

    /**
     * Create a LeadCustomForm record if the field belongs to the "Lead" group.
     */
    private function lead($customField)
    {
        $lead = CustomFieldGroup::where('name', 'Lead')->first();

        if ($customField->custom_field_group_id != $lead->id) {
            return false;
        }

        $leadField = new LeadCustomForm();
        $leadField->required = ($customField->required == 'yes') ? 1 : 0;
        $leadField->field_display_name = str($customField->label);
        $leadField->custom_fields_id = $customField->id;
        $leadField->field_name = $customField->name;
        $leadField->field_order = LeadCustomForm::max('field_order');
        $leadField->save();
    }

    /**
     * Create a TicketCustomForm record if the field belongs to the "Ticket" group.
     */
    private function ticket($customField)
    {
        $ticket = CustomFieldGroup::where('name', 'Ticket')->first();

        if ($customField->custom_field_group_id != $ticket->id) {
            return false;
        }

        $ticketField = new TicketCustomForm();
        $ticketField->required = ($customField->required == 'yes') ? 1 : 0;
        $ticketField->field_display_name = str($customField->label);
        $ticketField->custom_fields_id = $customField->id;
        $ticketField->field_name = $customField->name;
        $ticketField->field_type = $customField->type;
        $ticketField->field_order = TicketCustomForm::max('field_order');
        $ticketField->save();
    }

    /**
     * Handle the "updated" event.
     *
     * Syncs updates made to CustomField with related LeadCustomForm or
     * TicketCustomForm. Also ensures that deleted select options are
     * removed from existing custom_fields_data entries.
     */
    public function updated(CustomField $customField)
    {
        // Sync with LeadCustomForm
        $lead = CustomFieldGroup::where('name', 'Lead')->first();
        if ($customField->custom_field_group_id == $lead->id) {
            $id = $customField->id;
            $leadField = LeadCustomForm::firstWhere('custom_fields_id', $id);
            $leadField->required = ($customField->required == 'yes') ? 1 : 0;
            $leadField->field_display_name = str($customField->label);
            $leadField->field_name = $customField->name;
            $leadField->save();
        }

        // Sync with TicketCustomForm
        $ticket = CustomFieldGroup::where('name', 'Ticket')->first();
        if ($customField->custom_field_group_id === $ticket->id) {
            $id = $customField->id;
            $ticketField = TicketCustomForm::firstWhere('custom_fields_id', $id);

            $ticketField->required = ($customField->required == 'yes') ? 1 : 0;
            $ticketField->field_display_name = str($customField->label);
            $ticketField->custom_fields_id = $customField->id;
            $ticketField->field_name = $customField->name;
            $ticketField->field_type = $customField->type;
            $ticketField->save();
        }

        // Clean up invalid select values
        if ($customField->type == 'select') {
            $valuesIndexCount = count(json_decode($customField->values)) - 1;

            // Delete old values with indexes greater than available options
            DB::table('custom_fields_data')
                ->where('custom_field_id', $customField->id)
                ->where('value', '>', $valuesIndexCount)
                ->delete();
        }
    }
}
