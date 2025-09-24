<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\UserInvitation
 *
 * Represents an invitation sent to a user.
 *
 * @property int $id                         // Invitation ID
 * @property int $user_id                    // ID of the user who created the invitation
 * @property string $invitation_type         // Type of invitation (e.g., "email", "link")
 * @property string|null $email              // Email address for the invitation
 * @property string $invitation_code         // Unique code for the invitation
 * @property string $status                  // Status of the invitation (e.g., "pending", "accepted")
 * @property string|null $email_restriction  // Optional email restriction
 * @property string|null $message            // Optional message sent with the invitation
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when invitation was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when invitation was updated
 * @property int|null $company_id            // Company ID if multi-tenant
 * @property-read \App\Models\User $user    // Related user
 * @property-read \App\Models\Company|null $company // Related company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications // Related notifications
 * @property-read int|null $notifications_count // Count of notifications
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation newQuery()      // Start a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation query()         // Get query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereCreatedAt($value) // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereEmail($value) // Filter by email
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereEmailRestriction($value) // Filter by email_restriction
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereId($value) // Filter by ID
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereInvitationCode($value) // Filter by invitation_code
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereInvitationType($value) // Filter by invitation_type
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereMessage($value) // Filter by message
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereStatus($value) // Filter by status
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereUpdatedAt($value) // Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereUserId($value) // Filter by user_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereCompanyId($value) // Filter by company_id
 * @mixin \Eloquent
 */
class UserInvitation extends BaseModel
{
    use Notifiable, HasCompany; // Add notification and multi-company support

    protected $guarded = ['id']; // Prevent mass assignment on ID

    /**
     * Relationship: Get the user who created the invitation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
