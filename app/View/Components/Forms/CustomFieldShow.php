<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomFieldShow extends Component
{
    public $field;  // Custom field data to display
    public $value;  // Value of the custom field
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $field The custom field to display
     * @param mixed $value The value of the custom field
     */
    public function __construct($field, $value)
    {
        $this->field = $field;  // Set the custom field data
        $this->value = $value;  // Set the value of the custom field
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.custom-field-show');  // Render the view for displaying the custom field
    }
}
