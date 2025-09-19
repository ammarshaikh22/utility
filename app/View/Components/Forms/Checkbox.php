<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public $label;  // Label for the checkbox
    public $value;  // Value of the checkbox
    
    /**
     * Create a new component instance.
     * 
     * @param string $label The label for the checkbox
     * @param string $value The value of the checkbox
     */
    public function __construct($label, $value)
    {
        $this->label = $label;  // Set the checkbox label
        $this->value = $value;  // Set the checkbox value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.checkbox');  // Render the checkbox view
    }
}
