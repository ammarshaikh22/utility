<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TabSection extends Component
{
    /**
     * Create a new component instance.
     *
     * The constructor is currently empty but can be used in the future if needed.
     */
    public function __construct()
    {
        // Empty constructor, can be used for initialization if needed in the future
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the tab section.
     */
    public function render()
    {
        // Return the view for the 'tab-section' component
        return view('components.tab-section');
    }
}
