<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Ticket extends Component
{
    public $ticket;  // Ticket details (could be an object or array)
    public $status;  // Status of the ticket (e.g., open, closed)
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $ticket Ticket details (could be an array or object)
     * @param string $status The status of the ticket
     */
    public function __construct($ticket, $status)
    {
        $this->ticket = $ticket;  // Set the ticket details
        $this->status = $status;  // Set the status
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.ticket');  // Render the ticket view
    }
}
