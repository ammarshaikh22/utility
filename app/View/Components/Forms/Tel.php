<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tel extends Component
{
    public $tel;  // Telephone number to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param string $tel Telephone number to be displayed
     */
    public function __construct($tel)
    {
        $this->tel = $tel;  // Set the telephone number
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.tel');  // Render the telephone number view
    }
}
