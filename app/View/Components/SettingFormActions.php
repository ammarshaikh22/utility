<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingFormActions extends Component
{
    /**
     * Create a new component instance.
     *
     * The constructor is currently empty but can be used in the future if needed.
     */
    public function __construct()
    {
        // Empty constructor, can be used for initialization if needed in the future
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string - The view for this component that represents the setting form actions (e.g., submit, reset).
     */
    public function render()
    {
        // Return the view for the 'setting-form-actions' component
        return view('components.setting-form-actions');
    }
}
