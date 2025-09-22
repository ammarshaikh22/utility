<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Email extends Component
{
    public $email;  // Email address to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param string $email Email address to display
     */
    public function __construct($email)
    {
        $this->email = $email;  // Set the email address
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.email');  // Render the email view
    }
}
