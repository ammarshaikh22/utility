<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class DailyTimeLogReport extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $user;
    public $todayDate;
    public $role;

    public function __construct(User $user, $role)
    {
        // Initialize the notification with user, role, and company data
        $this->user = $user;
        $this->role = $role;
        $this->company = $this->user->company;
        $this->todayDate = Carbon::now()->toDateString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Specify mail as the delivery channel
        return ['mail'];
    }

    /**
     * Define the attachments for the notification.
     *
     * @return array
     */
    public function attachments()
    {
        // Return a PDF attachment for the timelog report
        return [
            Attachment::fromData(fn() => $this->domPdfObjectForDownload()['pdf']->output(), 'TimeLog-Report-' . $this->todayDate . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Generate the PDF object for the timelog report.
     *
     * @return array
     */
    public function domPdfObjectForDownload()
    {
        $company = $this->company;

        // Query employees with their timelogs for the specified date
        $employees = User::select('users.id', 'users.name')
            ->with(['timeLogs' => function ($query) use ($company) {
                $query->whereRaw('DATE(start_time) = ?', [$this->todayDate]);
                $query->where('company_id', $company->id);
            }, 'timeLogs.breaks'])
            ->when($this->role->name != 'admin', function ($query) {
                $query->where('users.id', $this->user->id);
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')->onlyEmployee()
            ->where('roles.company_id', $company->id)
            ->groupBy('users.id');

        $employees = $employees->get();

        // Process employee data to calculate timelog and break minutes
        $employeeData = [];
        foreach ($employees as $employee) {
            $employeeData[$employee->name] = [];
            $employeeData[$employee->name]['timelog'] = 0;
            $employeeData[$employee->name]['timelogBreaks'] = 0;

            if (count($employee->timeLogs) > 0) {
                foreach ($employee->timeLogs as $timeLog) {
                    $employeeData[$employee->name]['timelog'] += $timeLog->total_minutes;

                    if (count($timeLog->breaks) > 0) {
                        foreach ($timeLog->breaks as $timeLogBreak) {
                            $employeeData[$employee->name]['timelogBreaks'] += $timeLogBreak->total_minutes;
                        }
                    }
                }
            }
        }

        $now = $this->todayDate;
        $requestedDate = $now;

        // Create a PDF instance with landscape orientation
        $pdf = app('dompdf.wrapper')->setPaper('A4', 'landscape');

        // Enable PHP execution in the PDF
        $options = $pdf->getOptions();
        $options->set(['enable_php' => true]);
        $pdf->getDomPDF()->setOptions($options);
        /** @phpstan-ignore-line */

        // Load the timelog report view into the PDF
        $pdf->loadView('timelog-report', ['employees' => $employeeData, 'date' => $now, 'company' => $company]);

        $filename = 'timelog-report';

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Build the base notification message
        $build = parent::build($notifiable);

        // Generate PDF and attach it to the email
        $pdfOption = $this->domPdfObjectForDownload();
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];
        $build->attachData($pdf->output(), $filename . '.pdf');

        // Set the locale for the email
        App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

        // Configure the mail message with subject and template data
        $build->subject(__('email.dailyTimelogReport.subject') . ' ' . $this->todayDate)
            ->markdown('mail.timelog.timelog-report', ['date' => $this->todayDate, 'name' => $this->user->name]);

        // Reset the locale after building the message
        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Return an empty array
        return [];
    }
}