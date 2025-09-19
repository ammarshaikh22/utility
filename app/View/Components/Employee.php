<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Employee extends Component
{
    // Public properties to hold the user data and disabled link state
    public $user;
    public $disabledLink;

    /**
     * Create a new component instance.
     *
     * @param mixed $user - The user data to be displayed in the component.
     * @param mixed $disabledLink - An optional parameter to determine if the link should be disabled (default is null).
     */
    public function __construct($user, $disabledLink = null)
    {
        // Assign the provided user data and disabled link state to the component's properties
        $this->user = $user;
        $this->disabledLink = $disabledLink;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the employee's information.
     */
    public function render()
    {
        // Return the view for the 'employee' component
        return view('components.employee');
    }
}
