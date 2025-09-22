<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datepicker extends Component
{
    public $value;  // The selected date value
    
    /**
     * Create a new component instance.
     * 
     * @param string $value The selected date value
     */
    public function __construct($value)
    {
        $this->value = $value;  // Set the selected date value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.datepicker');  // Render the datepicker view
    }
}
