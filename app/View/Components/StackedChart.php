<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StackedChart extends Component
{
    // Public property to hold the chart data for the stacked chart
    public $chartData;

    /**
     * Create a new component instance.
     *
     * @param mixed $chartData - The data to be used to render the stacked chart.
     */
    public function __construct($chartData)
    {
        // Assign the provided chart data to the component's property
        $this->chartData = $chartData;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the stacked chart.
     */
    public function render()
    {
        // Return the view for the 'stacked-chart' component
        return view('components.stacked-chart');
    }
}
