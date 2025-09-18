<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TabItem extends Component
{
    // Public properties to hold the link and active state for the tab item
    public $link;
    public $active;

    /**
     * Create a new component instance.
     *
     * @param string $link - The URL or link for the tab item.
     * @param bool $active - A flag indicating if the tab item is active (default is false).
     */
    public function __construct($link, $active = false)
    {
        // Assign the provided link and active flag to the component's properties
        $this->link = $link;
        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the tab item.
     */
    public function render()
    {
        // Return the view for the 'tab-item' component
        return view('components.tab-item');
    }
}
