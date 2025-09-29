<?php

/**
 * Trait ProjectProgress
 *
 * Provides functionality to calculate and update project progress
 * based on the number of tasks completed versus total tasks.
 *
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 13/07/17
 * Time: 4:53 PM
 */

namespace App\Traits;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskboardColumn;

trait ProjectProgress
{
    /**
     * Calculate and update the progress of a project.
     *
     * @param int $projectId                  The ID of the project to calculate progress for.
     * @param string $projectProgress         Override flag ('true' forces recalculation).
     * @return float|bool|string|null         Returns percentage complete, "0" if no tasks,
     *                                        false if invalid input, or null if conditions not met.
     */
    public function calculateProjectProgress($projectId, $projectProgress = 'false')
    {
        // Fetch the project, including soft-deleted ones.
        $project = Project::withTrashed()->findOrFail($projectId);

        // Only proceed if project is set to calculate progress OR forced by argument.
        if (!is_null($project) && ($project->calculate_task_progress == 'true' || $projectProgress == 'true')) {
            // Get the column that represents completed tasks.
            $taskBoardColumn = TaskboardColumn::completeColumn();

            // Extra safety check if projectId is missing.
            if (is_null($projectId)) {
                return false;
            }

            // Count all tasks for this project.
            $totalTasks = Task::where('project_id', $projectId)->count();

            // If no tasks exist, project progress is 0%.
            if ($totalTasks == 0) {
                return '0';
            }

            // Count only completed tasks (those in the "complete" column).
            $completedTasks = Task::where('project_id', $projectId)
                ->where('tasks.board_column_id', $taskBoardColumn->id)
                ->count();

            // Calculate percentage completion.
            $percentComplete = ($completedTasks / $totalTasks) * 100;

            // Update the project with calculated progress.
            $project->completion_percent = $percentComplete;
            $project->save();

            return $percentComplete;
        }
    }
}
