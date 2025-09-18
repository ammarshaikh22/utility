<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GaugeChart extends Component
{
    // Public properties to hold the value and width of the gauge chart
    public $value;
    public $width;

    /**
     * Create a new component instance.
     *
     * @param int $value - The value to be represented in the gauge chart.
     * @param int $width - The width of the gauge chart.
     */
    public function __construct($value, $width)
    {
        // Assign the provided value and width to the component's properties
        $this->value = $value;
        $this->width = $width;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the gauge chart.
     */
    public function render()
    {
        // Return the view for the 'gauge-chart' component
        return view('components.gauge-chart');
    }
}
