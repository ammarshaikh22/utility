<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Password extends Component
{
    public $label;  // Label for the password input field
    public $value;  // Value of the password input
    
    /**
     * Create a new component instance.
     * 
     * @param string $label Label for the password input field
     * @param string $value Default value for the password input field
     */
    public function __construct($label, $value)
    {
        $this->label = $label;  // Set the label
        $this->value = $value;  // Set the default value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.password');  // Render the password field view
    }
}
