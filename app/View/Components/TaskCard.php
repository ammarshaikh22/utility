<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskCard extends Component
{
    // Public properties to hold task data, draggable state, and company data
    public $task;
    public $draggable;
    public $company;

    /**
     * Create a new component instance.
     *
     * @param mixed $task - The task data to be displayed on the task card.
     * @param string $draggable - A flag indicating if the task card is draggable (default is 'true').
     * @param mixed $company - The company associated with the task card.
     */
    public function __construct($task, $draggable = 'true', $company)
    {
        // Assign the provided task data, draggable flag, and company data to the component's properties
        $this->task = $task;
        $this->draggable = $draggable;
        $this->company = $company;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the task card.
     */
    public function render()
    {
        // Return the view for the 'task-card' component
        return view('components.cards.task-card');
    }
}
