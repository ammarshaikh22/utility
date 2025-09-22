<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterBox extends Component
{
    public $filters;  // Array of filter options
    
    /**
     * Create a new component instance.
     * 
     * @param array $filters Array containing filter options
     */
    public function __construct($filters)
    {
        $this->filters = $filters;  // Set the filter options
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.filter-box');  // Render the filter box view
    }
}
