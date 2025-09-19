<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    // Public property to hold the head type for the table (e.g., header type)
    public $headType = '';

    /**
     * Create a new component instance.
     *
     * @param string $headType - The type of table header (default is an empty string).
     */
    public function __construct($headType = '')
    {
        // Assign the provided head type to the component's property
        $this->headType = $headType;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the table.
     */
    public function render()
    {
        // Return the view for the 'table' component
        return view('components.table');
    }
}
