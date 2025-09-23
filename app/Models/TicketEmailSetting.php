<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\TicketEmailSetting
 *
 * Represents email configurations for ticketing system to handle incoming/outgoing emails.
 *
 * @property int $id
 * @property int|null $company_id          // The company this email setting belongs to
 * @property string|null $mail_username    // Email username for authentication
 * @property string|null $mail_password    // Password for email authentication
 * @property string|null $mail_from_name   // Display name for outgoing emails
 * @property string|null $mail_from_email  // Email address for outgoing emails
 * @property string|null $imap_host        // IMAP server host for incoming emails
 * @property string|null $imap_port        // IMAP server port
 * @property string|null $imap_encryption  // IMAP encryption type (ssl, tls)
 * @property int $status                    // Status (active/inactive)
 * @property int $verified                  // Whether email settings are verified (1 = yes, 0 = no)
 * @property int $sync_interval             // Interval (in minutes) for syncing emails
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relations:
 * @property-read \App\Models\Company|null $company    // The company linked to these settings
 *
 * @mixin \Eloquent
 */
class TicketEmailSetting extends BaseModel
{
    use HasCompany;

    // Protect the ID field from mass assignment
    protected $guarded = ['id'];
}
