<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Form extends Component
{
    // Public properties to hold the form method and spoof method flag
    public $spoofMethod = false;
    public $method;

    /**
     * Create a new component instance.
     *
     * @param string $method - The HTTP method for the form (default is 'POST').
     */
    public function __construct($method = 'POST')
    {
        // Assign the provided method to the component's method property
        $this->method = $method;

        // Set the spoof method flag if the method is PUT, PATCH, or DELETE
        $this->spoofMethod = in_array($this->method, ['PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the form.
     */
    public function render()
    {
        // Return the view for the 'form' component
        return view('components.form');
    }
}
