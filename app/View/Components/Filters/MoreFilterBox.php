<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MoreFilterBox extends Component
{
    public $filters;  // Array of additional filter options
    
    /**
     * Create a new component instance.
     * 
     * @param array $filters Array containing additional filter options
     */
    public function __construct($filters)
    {
        $this->filters = $filters;  // Set the additional filter options
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.more-filter-box');  // Render the more filter box view
    }
}
