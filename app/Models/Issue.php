<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Issue
 *
 * Represents an issue/bug/task in a project.
 * Issues can belong to projects, users, and companies,
 * and can also be notified via Laravel's notification system.
 *
 * @property int $id Unique identifier for the issue
 * @property string $description Issue description/details
 * @property int|null $user_id User responsible for the issue
 * @property int|null $project_id Project this issue belongs to
 * @property string $status Current status (e.g., pending, resolved)
 * @property \Illuminate\Support\Carbon|null $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at Last update timestamp
 * @property int|null $company_id Company this issue belongs to
 *
 * @property-read mixed $icon Dynamic icon property (if defined)
 * @property-read \App\Models\Project|null $project Related project
 * @property-read \App\Models\Company|null $company Related company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications Notifications for this issue
 * @property-read int|null $notifications_count Count of notifications
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue query()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Issue extends BaseModel
{
    use Notifiable, HasCompany;

    /**
     * Get the project associated with this issue.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Fetch all pending issues for a given project.
     *
     * @param int $projectId Project ID
     * @param int|null $userID (Optional) Filter issues assigned to a specific user
     * @return \Illuminate\Database\Eloquent\Collection|Issue[]
     */
    public static function projectIssuesPending($projectId, $userID = null)
    {
        $projectIssue = Issue::where('project_id', $projectId);

        // If a specific user is provided, filter by user_id
        if ($userID) {
            $projectIssue = $projectIssue->where('user_id', '=', $userID);
        }

        // Return only pending issues
        return $projectIssue->where('status', 'pending')->get();
    }

    /**
     * Check if the current issue belongs to the logged-in client user.
     *
     * @return bool True if the issue belongs to the authenticated user
     */
    public function checkIssueClient(): bool
    {
        $issue = Issue::where('id', $this->id)
            ->where('user_id', user()->id)
            ->count();

        return $issue > 0;
    }
}
