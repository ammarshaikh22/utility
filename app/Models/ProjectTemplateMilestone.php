<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;

/**
 * App\Models\ProjectTemplateMilestone
 *
 * Represents a milestone in a project template.
 * A milestone groups related tasks, defines start/end dates, cost, and status.
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $currency_id
 * @property string $milestone_title
 * @property string $summary
 * @property float $cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $invoice_created
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * 
 * Relationships:
 * @property-read \App\Models\Currency|null $currency
 * @property-read \App\Models\ProjectTemplate|null $projectTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateTask[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $completeTasks
 * 
 * @mixin \Eloquent
 */
class ProjectTemplateMilestone extends BaseModel
{
    use HasCompany; // Adds company-related global scope/logic (e.g., company_id filtering)

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_template_milestone';

    /**
     * Cast date fields to Carbon instances for easy manipulation.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    /**
     * Relationship: Milestone belongs to a currency.
     * Used to represent the cost in a particular currency.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Relationship: Milestone belongs to a project template.
     * Each milestone is tied to a specific project template.
     */
    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    /**
     * Relationship: Milestone has many tasks.
     * These tasks are specific to the project template milestone.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTemplateTask::class, 'milestone_id');
    }

    /**
     * Relationship: Milestone has many completed tasks.
     * Uses TaskboardColumn::completeColumn() to fetch only tasks
     * that are in the "completed" status column.
     */
    public function completeTasks(): HasMany
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();

        return $this->hasMany(Task::class, 'milestone_id')
                    ->where('tasks.board_column_id', $taskBoardColumn->id);
    }

    /**
     * Calculate the percentage of completed tasks for this milestone.
     *
     * @return float Completion percentage (0-100).
     */
    public function completionPercent(): float
    {
        $taskBoardColumn = TaskboardColumn::completeColumn();
        $tasks = $this->tasks()->count();

        if ($tasks === 0) {
            return 0; // Avoid division by zero if no tasks exist
        }

        $completedTasks = $this->tasks()
            ->where('tasks.board_column_id', $taskBoardColumn->id)
            ->count();

        return ($completedTasks / $tasks) * 100;
    }
}
