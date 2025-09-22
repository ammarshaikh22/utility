<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\GoogleCalendarModule
 *
 * Represents the Google Calendar module settings for the application.
 * This model defines which features (leads, tasks, holidays, etc.)
 * are integrated with Google Calendar.
 *
 * @property int $id Primary key
 * @property int $lead_status Indicates if lead integration is enabled
 * @property int $leave_status Indicates if leave integration is enabled
 * @property int $invoice_status Indicates if invoice integration is enabled
 * @property int $contract_status Indicates if contract integration is enabled
 * @property int $task_status Indicates if task integration is enabled
 * @property int $event_status Indicates if event integration is enabled
 * @property int $holiday_status Indicates if holiday integration is enabled
 * @property \Illuminate\Support\Carbon|null $created_at Record creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at Record update timestamp
 * @property int|null $company_id Company reference (multi-tenant support)
 *
 * @property-read \App\Models\Company|null $company Related company model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereContractStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereEventStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereHolidayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereInvoiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereLeadStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereLeaveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class GoogleCalendarModule extends BaseModel
{
    use HasCompany; // Trait for handling company-level scoping

    /**
     * The database table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_calendar_modules';
}
