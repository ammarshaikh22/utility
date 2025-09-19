<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BarChart extends Component
{
    // Public properties to hold the chart data, multiple bar flag, and space ratio
    public $chartData;
    public $multiple;
    public $spaceRatio;

    /**
     * Create a new component instance.
     *
     * @param mixed $chartData - The data used to render the bar chart.
     * @param bool $multiple - A flag to indicate if multiple bars should be rendered (default is false).
     * @param string $spaceRatio - A value representing the space ratio between bars (default is '0.2').
     */
    public function __construct($chartData, $multiple = false, $spaceRatio = '0.2')
    {
        // Assign the provided chart data, multiple flag, and space ratio to the component's properties
        $this->chartData = $chartData;
        $this->multiple = $multiple;
        $this->spaceRatio = $spaceRatio;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the bar chart.
     */
    public function render()
    {
        // Return the view for the 'bar-chart' component
        return view('components.bar-chart');
    }
}
