<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Gender extends Component
{
    // Public property to hold the gender data
    public $gender;

    /**
     * Create a new component instance.
     *
     * @param string $gender - The gender to be displayed (e.g., 'male', 'female').
     */
    public function __construct($gender)
    {
        // Assign the provided gender to the component's property
        $this->gender = $gender;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string - The view for this component that represents the gender.
     */
    public function render()
    {
        // Return the view for the 'gender' component
        return view('components.gender');
    }
}
