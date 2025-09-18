<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PieChart extends Component
{
    // Public properties to hold the chart data: labels, values, colors, and fullscreen option
    public $labels;
    public $values;
    public $colors;
    public $fullscreen;

    /**
     * Create a new component instance.
     *
     * @param array $labels - The labels for the pie chart segments.
     * @param array $values - The values corresponding to each label.
     * @param array $colors - The colors to be used for the pie chart segments.
     * @param bool $fullscreen - A flag indicating whether the chart should be displayed in fullscreen (default is false).
     */
    public function __construct($labels, $values, $colors, $fullscreen = false)
    {
        // Assign the provided labels, values, colors, and fullscreen flag to the component's properties
        $this->labels = $labels;
        $this->values = $values;
        $this->colors = $colors;
        $this->fullscreen = $fullscreen;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the pie chart.
     */
    public function render()
    {
        // Return the view for the 'pie-chart' component
        return view('components.pie-chart');
    }
}
