<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ToggleSwitch extends Component
{
    public $label;  // Label for the toggle switch
    public $checked;  // Whether the toggle switch is checked or not
    
    /**
     * Create a new component instance.
     * 
     * @param string $label Label for the toggle switch
     * @param bool $checked Whether the toggle switch is checked (default is false)
     */
    public function __construct($label, $checked = false)
    {
        $this->label = $label;  // Set the label
        $this->checked = $checked;  // Set the checked state
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.toggle-switch');  // Render the toggle switch view
    }
}
