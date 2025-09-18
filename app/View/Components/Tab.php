<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tab extends Component
{
    // Public properties to hold the href, text, and ajax data
    public $href;
    public $text;
    public $ajax;

    /**
     * Create a new component instance.
     *
     * @param string $href - The URL or link for the tab.
     * @param string $text - The text to be displayed for the tab.
     * @param string $ajax - A flag indicating if the tab should use AJAX (default is 'true').
     */
    public function __construct($href, $text, $ajax = 'true')
    {
        // Assign the provided href, text, and ajax values to the component's properties
        $this->href = $href;
        $this->text = $text;
        $this->ajax = $ajax;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the tab.
     */
    public function render()
    {
        // Return the view for the 'tab' component
        return view('components.tab');
    }
}
