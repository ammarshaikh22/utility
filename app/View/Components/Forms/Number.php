<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Number extends Component
{
    public $value;  // The number value to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param int $value The number value
     */
    public function __construct($value)
    {
        $this->value = $value;  // Set the number value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.number');  // Render the number view
    }
}
