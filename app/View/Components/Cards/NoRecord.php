<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NoRecord extends Component
{
    public $icon;
    public $message;

    /**
     * Create a new component instance.
     *
     * @param string $icon The icon to display.
     * @param string $message The message to display.
     */
    public function __construct($icon, $message)
    {
        $this->icon = $icon;  // Initialize icon property
        $this->message = $message;  // Initialize message property
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.no-record');  // Render the view for this component
    }
}
