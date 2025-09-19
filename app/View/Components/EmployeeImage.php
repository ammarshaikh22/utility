<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmployeeImage extends Component
{
    // Public property to hold the user data (specifically for the employee image)
    public $user;

    /**
     * Create a new component instance.
     *
     * @param mixed $user - The user data, specifically for the employee image.
     */
    public function __construct($user)
    {
        // Assign the provided user data to the component's user property
        $this->user = $user;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the employee's image.
     */
    public function render()
    {
        // Return the view for the 'employee-image' component
        return view('components.employee-image');
    }
}
