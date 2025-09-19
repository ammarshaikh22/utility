<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserOption extends Component
{
    // Public properties to hold the user data, selection state, and additional options
    public $user;
    public $selected;
    public $pill;
    public $agent;
    public $userID;
    public $additionalText;

    /**
     * Create a new component instance.
     *
     * @param mixed $user - The user data for the option.
     * @param bool $selected - A flag indicating if the option is selected (default is false).
     * @param bool $pill - A flag indicating if the pill should be displayed (default is false).
     * @param bool $agent - A flag indicating if the agent is associated with the user (default is false).
     * @param mixed $userID - The ID of the user (default is null).
     * @param string|null $additionalText - Any additional text to be displayed with the user option (default is null).
     */
    public function __construct($user, $selected = false, $pill = false, $agent = false, $userID = null, $additionalText = null)
    {
        // Assign the provided values to the component's properties
        $this->user = $user;
        $this->selected = $selected;
        $this->pill = $pill;
        $this->agent = $agent;
        $this->userID = $userID;
        $this->additionalText = $additionalText;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the user option.
     */
    public function render()
    {
        // Return the view for the 'user-option' component
        return view('components.user-option');
    }
}
