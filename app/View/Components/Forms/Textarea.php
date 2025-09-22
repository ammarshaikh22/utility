<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Textarea extends Component
{
    public $label;  // Label for the textarea field
    public $value;  // Value for the textarea
    
    /**
     * Create a new component instance.
     * 
     * @param string $label Label for the textarea field
     * @param string $value Default value for the textarea
     */
    public function __construct($label, $value)
    {
        $this->label = $label;  // Set the label
        $this->value = $value;  // Set the value
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.textarea');  // Render the textarea view
    }
}
