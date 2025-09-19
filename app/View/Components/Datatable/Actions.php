<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Actions extends Component
{
    public $actions;  // Array of actions to display (e.g., buttons, links)
    
    /**
     * Create a new component instance.
     * 
     * @param array $actions Array containing actions to display
     */
    public function __construct($actions)
    {
        $this->actions = $actions;  // Initialize actions array
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.actions');  // Render the actions view
    }
}
