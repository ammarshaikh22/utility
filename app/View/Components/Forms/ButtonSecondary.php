<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonSecondary extends Component
{
    public $label;  // The label for the secondary button
    
    /**
     * Create a new component instance.
     * 
     * @param string $label The label for the secondary button
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
        return view('components.button-secondary');  // Render the secondary button view
    }
}
