<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Text extends Component
{
    public $text;  // Text content to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param string $text Text content to be displayed
     */
    public function __construct($text)
    {
        $this->text = $text;  // Set the text content
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.text');  // Render the text content view
    }
}
