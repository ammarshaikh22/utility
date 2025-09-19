<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Radio extends Component
{
    public $label;  // Label for the radio button
    public $value;  // Value for the radio button
    public $checked;  // Whether the radio button is checked or not
    
    /**
     * Create a new component instance.
     * 
     * @param string $label Label for the radio button
     * @param string $value Value for the radio button
     * @param bool $checked Whether the radio button is checked (default is false)
     */
    public function __construct($label, $value, $checked = false)
    {
        $this->label = $label;  // Set the label
        $this->value = $value;  // Set the value
        $this->checked = $checked;  // Set the checked state
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.radio');  // Render the radio button view
    }
}
