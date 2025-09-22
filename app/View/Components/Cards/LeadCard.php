<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LeadCard extends Component
{
    public $lead;  // Lead details
    public $draggable;  // Whether the lead card is draggable or not
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $lead Details of the lead
     * @param string $draggable Whether the card is draggable (default 'true')
     */
    public function __construct($lead, $draggable = 'true')
    {
        $this->lead = $lead;  // Set lead details
        $this->draggable = $draggable;  // Set draggable property
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.lead-card');  // Render the lead card view
    }
}
