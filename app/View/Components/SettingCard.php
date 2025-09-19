<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingCard extends Component
{
    // Public property to hold the HTTP method for the form (e.g., PUT, POST)
    public $method;

    /**
     * Create a new component instance.
     *
     * @param string $method - The HTTP method for the setting card form (default is 'PUT').
     */
    public function __construct($method = 'PUT')
    {
        // Assign the provided method to the component's property
        $this->method = $method;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the setting card.
     */
    public function render()
    {
        // Return the view for the 'setting-card' component
        return view('components.setting-card');
    }
}
