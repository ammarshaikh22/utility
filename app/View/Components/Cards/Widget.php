<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Widget extends Component
{
    public $widget;  // Widget details (could be an array or object)
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $widget Widget details (could be an array or object)
     */
    public function __construct($widget)
    {
        $this->widget = $widget;  // Set the widget details
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.widget');  // Render the widget view
    }
}
