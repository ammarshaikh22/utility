<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DataRow extends Component
{
    public $columns;  // Array of data columns for the row

    /**
     * Create a new component instance.
     * 
     * @param array $columns List of columns to display in the row
     */
    public function __construct($columns)
    {
        $this->columns = $columns;  // Set the columns property
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.data-row');  // Render the data row view
    }
}
