<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Data extends Component
{
    public $label;  // Label for the data entry
    public $value;  // Value associated with the data entry
    
    /**
     * Create a new component instance.
     * 
     * @param string $label Label for the data
     * @param string $value Value to be displayed
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
        return view('components.cards.data');  // Render the data entry view
    }
}
