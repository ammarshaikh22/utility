<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Select2Ajax extends Component
{
    public $url;  // URL for fetching select options via AJAX
    public $placeholder;  // Placeholder text for the select field
    
    /**
     * Create a new component instance.
     * 
     * @param string $url URL to fetch select options via AJAX
     * @param string $placeholder Placeholder text for the select field
     */
    public function __construct($url, $placeholder)
    {
        $this->url = $url;  // Set the URL for fetching options
        $this->placeholder = $placeholder;  // Set the placeholder text
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.select2-ajax');  // Render the Select2 AJAX dropdown view
    }
}
