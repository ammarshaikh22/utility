<?php

namespace App\Observers;

use App\Models\TaskboardColumn;
use App\Models\User;
use App\Models\UserTaskboardSetting;

class TaskBoardColumnObserver
{
    /**
     * Handle the "created" event.
     *
     * After a new TaskboardColumn is created:
     * - Fetch all employees in the system.
     * - For each employee, create a UserTaskboardSetting record
     *   so that every employee has access/visibility
     *   of the new taskboard column.
     */
    public function created(TaskboardColumn $taskboardColumn)
    {
        if (user()) {
            $employees = User::allEmployees();

            foreach ($employees as $item) {
                UserTaskboardSetting::create([
                    'user_id' => $item->id,
                    'board_column_id' => $taskboardColumn->id
                ]);
            }
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before inserting a new TaskboardColumn record,
     * automatically assign the `company_id`
     * from the currently active company context.
     */
    public function creating(TaskboardColumn $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
