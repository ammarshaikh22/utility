<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class LeadImport implements ToArray
{
    /**
     * Define the fields for lead import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'name', 'name' => __('modules.lead.clientName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('modules.lead.clientEmail'), 'required' => 'Yes'],
            ['id' => 'note', 'name' => __('app.note'), 'required' => 'No'],
            ['id' => 'company_name', 'name' => __('modules.lead.companyName'), 'required' => 'No'],
            ['id' => 'company_website', 'name' => __('modules.lead.website'), 'required' => 'No'],
            ['id' => 'mobile', 'name' => __('modules.lead.mobile'), 'required' => 'No'],
            ['id' => 'company_phone', 'name' => __('modules.client.officePhoneNumber'), 'required' => 'No'],
            ['id' => 'country', 'name' => __('app.country'), 'required' => 'No'],
            ['id' => 'state', 'name' => __('modules.stripeCustomerAddress.state'), 'required' => 'No'],
            ['id' => 'city', 'name' => __('modules.stripeCustomerAddress.city'), 'required' => 'No'],
            ['id' => 'postal_code', 'name' => __('modules.stripeCustomerAddress.postalCode'), 'required' => 'No'],
            ['id' => 'address', 'name' => __('app.address'), 'required' => 'No'],
            ['id' => 'source', 'name' => __('modules.lead.leadSource'), 'required' => 'No'],
            ['id' => 'created_at', 'name' => __('app.createdOn'), 'required' => 'No'],
        ];
    }

    /**
     * Return the input array as is.
     *
     * @param array $array The imported data array
     * @return array The input array
     */
    public function array(array $array): array
    {
        return $array;
    }
}