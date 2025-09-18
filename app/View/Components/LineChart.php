<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LineChart extends Component
{
    // Public properties to hold the chart data and the flag for multiple lines
    public $chartData;
    public $multiple;

    /**
     * Create a new component instance.
     *
     * @param mixed $chartData - The data to be used to render the line chart.
     * @param bool $multiple - A flag indicating whether multiple lines should be rendered (default is false).
     */
    public function __construct($chartData, $multiple = false)
    {
        // Assign the provided chart data and multiple flag to the component's properties
        $this->chartData = $chartData;
        $this->multiple = $multiple;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the line chart.
     */
    public function render()
    {
        // Return the view for the 'line-chart' component
        return view('components.line-chart');
    }
}
