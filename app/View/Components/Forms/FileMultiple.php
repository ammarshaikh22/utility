<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileMultiple extends Component
{
    public $files;  // Multiple files data to be displayed
    
    /**
     * Create a new component instance.
     * 
     * @param array $files Array of file data to display
     */
    public function __construct($files)
    {
        $this->files = $files;  // Set the multiple files data
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.file-multiple');  // Render the multiple files view
    }
}
