<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NoRecordFoundList extends Component
{
    public $colspan;

    /**
     * Create a new component instance.
     *
     * @param int $colspan The number of columns the "no record" message will span.
     */
    public function __construct($colspan = 3)
    {
        $this->colspan = $colspan;  // Initialize colspan property (default is 3)
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.no-record-found-list');  // Render the view for no record found list
    }
}
