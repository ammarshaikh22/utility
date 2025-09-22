<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Message extends Component
{
    public $message;  // Message content
    public $user;  // User who sent the message
    
    /**
     * Create a new component instance.
     * 
     * @param string $message The message content
     * @param string $user The user who sent the message
     */
    public function __construct($message, $user)
    {
        $this->message = $message;  // Set the message content
        $this->user = $user;  // Set the user who sent the message
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.message');  // Render the message view
    }
}
