<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Range extends Component
{
    public $min;  // Minimum value of the range
    public $max;  // Maximum value of the range
    public $value;  // Current value of the range
    
    /**
     * Create a new component instance.
     * 
     * @param int $min Minimum value of the range
     * @param int $max Maximum value of the range
     * @param int $value Current value of the range
     */
    public function __construct($min, $max, $value)
    {
        $this->min = $min;  // Set the minimum value
        $this->max = $max;  // Set the maximum value
        $this->value = $value;  // Set the current value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.range');  // Render the range input view
    }
}
