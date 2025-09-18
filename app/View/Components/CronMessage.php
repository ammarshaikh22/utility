<?php

namespace App\View\Components;

// Importing necessary classes for the component
use App\Models\GlobalSetting;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CronMessage extends Component
{
    /** 
     * @var false|mixed
     * This property holds the modal state, which is passed during the construction of the component.
     */
    private mixed $modal;

    /**
     * Constructor for the CronMessage component.
     *
     * @param mixed $modal - This is an optional parameter used to control modal behavior.
     */
    public function __construct($modal = false)
    {
        // Assign the modal value to the class property
        $this->modal = $modal;
    }

    /**
     * This method is used to render the component's view.
     * It retrieves the global settings and passes them along with the modal state to the view.
     *
     * @return View|string - The view for this component, which can either be a string or an actual view instance.
     */
    public function render()
    {
        // Fetch global settings from the database
        $globalSetting = GlobalSetting::select(['id', 'hide_cron_message', 'last_cron_run'])->first();

        // Store the modal state in a local variable
        $modal = $this->modal;

        // Return the view for the 'cron-message' component with the relevant data
        return view('components.cron-message', compact('globalSetting', 'modal'));
    }
}
 