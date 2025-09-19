<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ClientSearchOption extends Component
{
    // Public property to hold the user data related to the client search option
    public $user;

    /**
     * Create a new component instance.
     *
     * @param mixed $user - The user data for the client search option.
     */
    public function __construct($user)
    {
        // Assign the provided user data to the component's property
        $this->user = $user;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string - The view for this component that represents the client search option.
     */
    public function render()
    {
        // Return the view for the 'client-search-option' component
        return view('components.client-search-option');
    }
}
