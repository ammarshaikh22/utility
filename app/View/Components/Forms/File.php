<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class File extends Component
{
    public $file;  // File data to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param mixed $file The file data to display
     */
    public function __construct($file)
    {
        $this->file = $file;  // Set the file data
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.file');  // Render the file view
    }
}
