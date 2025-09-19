<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppTitle extends Component
{
    // Public property to hold the page title
    public $pageTitle;

    /**
     * Create a new component instance.
     *
     * @param string $pageTitle - The title to be displayed on the page.
     */
    public function __construct($pageTitle)
    {
        // Ensure that the page title is either an array or a translated string
        $this->pageTitle = is_array(__($pageTitle)) ? $pageTitle : __($pageTitle);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the page title.
     */
    public function render()
    {
        // Return the view for the 'app-title' component
        return view('components.app-title');
    }
}
