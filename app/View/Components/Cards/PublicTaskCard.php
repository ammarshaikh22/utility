<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PublicTaskCard extends Component
{
    public $task;  // Task details (could be an object or array)
    public $status;  // Status of the task (e.g., pending, completed)
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $task Task details (could be an object or array)
     * @param string $status Status of the task (default is 'pending')
     */
    public function __construct($task, $status = 'pending')
    {
        $this->task = $task;  // Set the task details
        $this->status = $status;  // Set the status of the task
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.public-task-card');  // Render the public task card view
    }
}
