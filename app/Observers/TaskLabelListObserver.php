<?php

namespace App\Observers;

use App\Models\ProjectTemplateTask;
use App\Models\Task;
use App\Models\TaskLabelList;

class TaskLabelListObserver
{
    /**
     * Handle the "creating" event.
     *
     * Before inserting a new TaskLabelList:
     * - Attach the label to the current company if available.
     */
    public function creating(TaskLabelList $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * Handle the "updated" event.
     *
     * When a TaskLabelList is updated:
     * - If the `project_id` changes and a `task_id` is present in the request,
     *   ensure consistency of labels in related ProjectTemplateTask and Task models.
     *
     * Steps:
     * 1. Retrieve all globally valid labels (those not tied to any project).
     * 2. For each ProjectTemplateTask:
     *    - Filter its task labels to only keep valid ones.
     *    - Update if changes are detected.
     * 3. For the requested Task:
     *    - If its project does not match the updated label’s project, 
     *      detach the label from the task.
     */
    public function updated($taskLabel)
    {
        if ($taskLabel->isDirty('project_id') && request()->task_id != null) {

            $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();
            $projectTemplateTasks = ProjectTemplateTask::all();

            foreach ($projectTemplateTasks as $task) {
                $taskLabelsArray = explode(',', $task->task_labels);

                // Keep only valid labels
                $updatedTaskLabels = array_filter($taskLabelsArray, function ($labelId) use ($validLabelIds) {
                    return in_array($labelId, $validLabelIds);
                });

                // Update if changes occurred
                if (implode(',', $updatedTaskLabels) !== $task->task_labels) {
                    $task->task_labels = implode(',', $updatedTaskLabels);
                    $task->save();
                }
            }

            // Ensure Task's labels align with the updated label's project
            $task = Task::with('labels')->findOrFail(request()->task_id);

            if ($task->project_id != $taskLabel->project_id) {
                $task->labels()->detach(request()->label_id);
            }
        }
    }

    /**
     * Handle the "deleted" event.
     *
     * When a TaskLabelList is deleted:
     * - Similar cleanup as the update process:
     *   - Re-check all ProjectTemplateTasks and remove invalid labels
     *     (labels tied to deleted projects).
     */
    public function deleted()
    {
        $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();
        $projectTemplateTasks = ProjectTemplateTask::all();

        foreach ($projectTemplateTasks as $task) {
            $taskLabelsArray = explode(',', $task->task_labels);

            // Keep only valid labels
            $updatedTaskLabels = array_filter($taskLabelsArray, function ($labelId) use ($validLabelIds) {
                return in_array($labelId, $validLabelIds);
            });

            // Update if changes occurred
            if (implode(',', $updatedTaskLabels) !== $task->task_labels) {
                $task->task_labels = implode(',', $updatedTaskLabels);
                $task->save();
            }
        }
    }
}
