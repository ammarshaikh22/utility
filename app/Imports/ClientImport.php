<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ClientImport implements ToArray
{
    /**
     * @var array Stores the processed data from the import
     */
    protected array $processedData = [];

    /**
     * Define the fields for client import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'name', 'name' => __('modules.client.clientName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('app.email'), 'required' => 'No'],
            ['id' => 'mobile', 'name' => __('app.mobile'), 'required' => 'No'],
            ['id' => 'gender', 'name' => __('modules.employees.gender'), 'required' => 'No'],
            ['id' => 'company_name', 'name' => __('modules.client.companyName'), 'required' => 'No'],
            ['id' => 'address', 'name' => __('modules.accountSettings.companyAddress'), 'required' => 'No'],
            ['id' => 'city', 'name' => __('modules.stripeCustomerAddress.city'), 'required' => 'No'],
            ['id' => 'state', 'name' => __('modules.stripeCustomerAddress.state'), 'required' => 'No'],
            ['id' => 'country_id', 'name' => __('modules.stripeCustomerAddress.country'), 'required' => 'No'],
            ['id' => 'postal_code', 'name' => __('modules.stripeCustomerAddress.postalCode'), 'required' => 'No'],
            ['id' => 'company_phone', 'name' => __('modules.client.officePhoneNumber'), 'required' => 'No'],
            ['id' => 'company_website', 'name' => __('modules.client.website'), 'required' => 'No'],
            ['id' => 'gst_number', 'name' => __('app.gstNumber'), 'required' => 'No'],
        ];
    }

    /**
     * Process the imported array and store it.
     *
     * @param array $array The imported data array
     * @return array The input array
     */
    public function array(array $array): array
    {
        $this->processedData = $array;
        return $array;
    }

    /**
     * Retrieve the processed data.
     *
     * @return array The processed data array
     */
    public function getProcessedData(): array
    {
        return $this->processedData;
    }
}