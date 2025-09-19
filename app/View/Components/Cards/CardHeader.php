<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardHeader extends Component
{
    public $title;  // Title of the card header
    public $subtitle;  // Optional subtitle for the card header

    /**
     * Create a new component instance.
     * 
     * @param string $title Title for the card header
     * @param string|null $subtitle Optional subtitle for the card header
     */
    public function __construct($title, $subtitle = null)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.card-header');  // Render the card header view
    }
}
    