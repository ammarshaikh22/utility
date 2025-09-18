<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateBadge extends Component
{
    // Public properties to hold the month and date values
    public $month;
    public $date;

    /**
     * Create a new component instance.
     *
     * @param string $month - The month to be displayed in the badge.
     * @param int $date - The date to be displayed in the badge.
     */
    public function __construct($month, $date)
    {
        // Assign the provided month and date to the component's properties
        $this->month = $month;
        $this->date = $date;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the date badge.
     */
    public function render()
    {
        // Return the view for the 'date-badge' component
        return view('components.date-badge');
    }
}
