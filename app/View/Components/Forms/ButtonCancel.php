<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonCancel extends Component
{
    public $label;  // The label for the cancel button
    
    /**
     * Create a new component instance.
     * 
     * @param string $label The label for the cancel button
     */
    public function __construct($label)
    {
        $this->label = $label;  // Set the button label
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.button-cancel');  // Render the cancel button view
    }
}
