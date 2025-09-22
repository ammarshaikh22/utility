<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Url extends Component
{
    public $url;  // URL to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param string $url URL to be displayed
     */
    public function __construct($url)
    {
        $this->url = $url;  // Set the URL
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.url');  // Render the URL view
    }
}
