<?php

namespace App\Observers;

use App\Models\CompanyAddress;
use App\Models\EmployeeDetails;

class CompanyAddressObserver
{
    // Triggered before creating a new CompanyAddress
    public function creating(CompanyAddress $model)
    {
        // If a company is available, associate the address with the company
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    // Triggered before deleting a CompanyAddress
    public function deleting(CompanyAddress $model)
    {
        // Get the default company address for the current company
        $companyAddress = CompanyAddress::where('is_default', 1)
            ->where('company_id', company()->id)
            ->first();

        // Update all employees linked to the deleting address
        // and reassign them to the default company address
        EmployeeDetails::where('company_address_id', $model->id)
            ->update(['company_address_id' => $companyAddress->id]);
    }
}
