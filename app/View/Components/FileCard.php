<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileCard extends Component
{
    // Public properties to hold the file name and date it was added
    public $fileName;
    public $dateAdded;

    /**
     * Create a new component instance.
     *
     * @param string $fileName - The name of the file to be displayed on the card.
     * @param string $dateAdded - The date when the file was added.
     */
    public function __construct($fileName, $dateAdded)
    {
        // Assign the provided file name and date to the component's properties
        $this->fileName = $fileName;
        $this->dateAdded = $dateAdded;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the file card.
     */
    public function render()
    {
        // Return the view for the 'file-card' component located in the 'components.cards' folder
        return view('components.cards.file-card');
    }
}
