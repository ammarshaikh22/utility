<?php

namespace App\Observers;

use App\Models\DashboardWidget;

class DashboardWidgetObserver
{
    /**
     * Handle the "creating" event for DashboardWidget.
     *
     * This ensures that when a new dashboard widget is created,
     * it is automatically linked to the current company by assigning
     * the `company_id`.
     *
     * Purpose:
     * - Enforces multi-tenancy (widgets belong to the correct company).
     * - Prevents creating widgets without a company context.
     */
    public function creating(DashboardWidget $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
