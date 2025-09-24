<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;

/**
 * App\Models\ProjectMilestone
 *
 * Represents a milestone within a project. Each milestone can have tasks,
 * costs, associated currency, and progress tracking.
 *
 * @property int $id
 * @property int|null $project_id ID of the related project
 * @property int|null $currency_id ID of the currency used for the milestone cost
 * @property string $milestone_title Title/name of the milestone
 * @property string $summary Short description/summary of the milestone
 * @property float $cost Budget or cost assigned to this milestone
 * @property string $status Current status of the milestone (e.g., pending, completed)
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when updated
 * @property int $invoice_created Flag to check if invoice is created for this milestone
 * @property int|null $invoice_id Related invoice ID
 * @property int|null $added_by User ID who added this milestone
 * @property int|null $last_updated_by User ID who last updated this milestone
 * @property \Illuminate\Support\Carbon|null $start_date Planned start date
 * @property \Illuminate\Support\Carbon|null $end_date Planned end date
 *
 * @property-read \App\Models\Currency|null $currency Related currency
 * @property-read \App\Models\Project|null $project Related project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks Tasks under this milestone
 * @property-read int|null $tasks_count Number of tasks
 * @property-read mixed $icon Placeholder for computed icon property
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereEndDate($value)
 * @mixin \Eloquent
 */
class ProjectMilestone extends BaseModel
{
    use HasCompany;

    /**
     * Cast attributes to specific data types
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    /**
     * Relation: Get the currency associated with this milestone
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Relation: Get the project this milestone belongs to
     * (including soft-deleted projects)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    /**
     * Relation: Get all tasks linked to this milestone
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    /**
     * Relation: Get only completed tasks linked to this milestone
     */
    public function completeTasks(): HasMany
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();
        return $this->hasMany(Task::class, 'milestone_id')
                    ->where('tasks.board_column_id', $taskBoardColumn->id);
    }

    /**
     * Calculate milestone completion percentage
     *
     * @return float Completion percentage (0–100)
     */
    public function completionPercent()
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        $tasks = $this->tasks()->count();
        if ($tasks === 0) {
            return 0; // Avoid division by zero
        }

        $completedTasks = $this->tasks()
            ->where('tasks.board_column_id', $taskBoardColumn->id)
            ->count();

        return ($completedTasks / $tasks) * 100;
    }
}
