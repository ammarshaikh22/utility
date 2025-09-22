<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Select extends Component
{
    public $options;  // Array of options to display in the select dropdown
    public $selected;  // The currently selected option
    
    /**
     * Create a new component instance.
     * 
     * @param array $options Array of options for the select dropdown
     * @param string $selected Currently selected option
     */
    public function __construct($options, $selected)
    {
        $this->options = $options;  // Set the options array
        $this->selected = $selected;  // Set the selected option
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.select');  // Render the select dropdown view
    }
}
