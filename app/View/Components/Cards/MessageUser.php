<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MessageUser extends Component
{
    public $message;  // The message associated with the user
    
    /**
     * Create a new component instance.
     * 
     * @param string $message The message content
     */
    public function __construct($message)
    {
        $this->message = $message;  // Set the message content
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.message-user');  // Render the message user view
    }
}
