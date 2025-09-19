<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LinkPrimary extends Component
{
    public $url;  // URL for the link
    public $text;  // Text for the link
    
    /**
     * Create a new component instance.
     * 
     * @param string $url URL for the link
     * @param string $text Text to display for the link
     */
    public function __construct($url, $text)
    {
        $this->url = $url;  // Set the link URL
        $this->text = $text;  // Set the link text
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.link-primary');  // Render the primary link view
    }
}
