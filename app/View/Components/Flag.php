<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Flag extends Component
{
    // Public property to hold the country name
    public $country;

    /**
     * Create a new component instance.
     *
     * @param string $country - The name of the country for which the flag will be displayed.
     */
    public function __construct($country)
    {
        // Assign the provided country name to the component's property
        $this->country = $country;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the flag of the specified country.
     */
    public function render()
    {
        // Return the view for the 'flag' component
        return view('components.flag');
    }
}
    