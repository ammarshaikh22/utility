<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputGroup extends Component
{
    public $prefix;  // Prefix text or icon for the input field
    public $suffix;  // Suffix text or icon for the input field
    
    /**
     * Create a new component instance.
     * 
     * @param string $prefix Prefix text or icon
     * @param string $suffix Suffix text or icon
     */
    public function __construct($prefix = null, $suffix = null)
    {
        $this->prefix = $prefix;  // Set the prefix value
        $this->suffix = $suffix;  // Set the suffix value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.input-group');  // Render the input group view
    }
}
