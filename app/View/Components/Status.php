<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Status extends Component
{
    // Public properties for the component
    public $style;   // Custom style to be applied (optional)
    public $color;   // The color to represent the status (default is 'red')
    public $value;   // The value or label of the status (e.g., "Active", "Inactive")

    /**
     * Create a new component instance.
     *
     * @param string $value  The value or label to be displayed (e.g., "Active", "Inactive")
     * @param string $style  (optional) Custom CSS style for the status (defaults to an empty string)
     * @param string $color  (optional) The color for the status (defaults to 'red')
     * @return void
     */
    public function __construct($value, $style = '', $color = 'red')
    {
        // Assign the provided values to the public properties
        $this->style = $style;
        $this->color = $color;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * This method returns the view that will render the status component.
     *
     * @return View|string  The view that will be used to display the status component
     */
    public function render()
    {
        // Return the view for the status component (located at resources/views/components/status.blade.php)
        return view('components.status');
    }
}
