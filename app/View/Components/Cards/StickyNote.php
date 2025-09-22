<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StickyNote extends Component
{
    public $content;  // The content of the sticky note
    public $color;    // The color of the sticky note
    
    /**
     * Create a new component instance.
     * 
     * @param string $content The content of the sticky note
     * @param string $color The color of the sticky note (default is yellow)
     */
    public function __construct($content, $color = 'yellow')
    {
        $this->content = $content;  // Set the content of the sticky note
        $this->color = $color;      // Set the color of the sticky note
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.sticky-note');  // Render the sticky note view
    }
}
