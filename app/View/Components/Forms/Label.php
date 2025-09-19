<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Label extends Component
{
    public $text;  // Label text to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param string $text The label text
     */
    public function __construct($text)
    {
        $this->text = $text;  // Set the label text
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.label');  // Render the label view
    }
}
