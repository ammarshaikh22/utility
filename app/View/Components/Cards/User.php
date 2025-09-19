<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class User extends Component
{
    public $user;  // User details (could be an array or object)
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $user User details (could be an object or array)
     */
    public function __construct($user)
    {
        $this->user = $user;  // Set the user details
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.user');  // Render the user view
    }
}
