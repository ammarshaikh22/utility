<?php

namespace App\Observers\SuperAdmin;

use App\Models\PackageUpdateNotify;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;

class PackageObserver
{
    // Before saving a Package, automatically set monthly_status and annual_status
    // if the package is free or default and not a lifetime package
    public function saving(Package $package)
    {
        if (($package->is_free || $package->default === 'yes') && $package->package != 'lifetime') {
            $package->monthly_status = 1;
            $package->annual_status = 1;
        }
    }

    // After updating a Package, update module settings for each company and handle notifications
    public function updated(Package $package)
    {
        $package->companies->each(function ($company) use ($package) {

            // If modules in the package changed, update module settings for the company
            if ($package->isDirty('module_in_package')) {
                (new CompanyObserver())->updateModuleSettings($company);
            }

            // Delete package update notifications if company's employees are within package limit
            $companyEmployeesCount = $company->employees()->count();
            if ($companyEmployeesCount <= $package->max_employees) {
                PackageUpdateNotify::where('company_id', $company->id)->delete();
            }

            // Clear cached package data for the company
            clearCompanyValidPackageCache($company->id);
        });
    }
}
