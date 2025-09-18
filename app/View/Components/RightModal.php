<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RightModal extends Component
{
    /**
     * Create a new component instance.
     *
     * The constructor is currently empty but can be used in the future if needed.
     */
    public function __construct()
    {
        // Empty constructor, can be used to initialize properties if needed
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the right modal.
     */
    public function render()
    {
        // Return the view for the 'right-modal' component
        return view('components.right-modal');
    }
}
