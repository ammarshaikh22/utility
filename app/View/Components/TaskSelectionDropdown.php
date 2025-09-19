<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskSelectionDropdown extends Component
{
    // Public properties to hold the task list and the field requirement flag
    public $tasks;
    public $fieldRequired;

    /**
     * Create a new component instance.
     *
     * @param array $tasks - The list of tasks to be displayed in the dropdown.
     * @param bool $fieldRequired - A flag indicating if the field is required (default is true).
     */
    public function __construct($tasks, $fieldRequired = true)
    {
        // Assign the provided tasks and fieldRequired flag to the component's properties
        $this->tasks = $tasks;
        $this->fieldRequired = $fieldRequired;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string - The view for this component that represents the task selection dropdown.
     */
    public function render()
    {
        // Return the view for the 'task-selection-dropdown' component
        return view('components.task-selection-dropdown');
    }
}
