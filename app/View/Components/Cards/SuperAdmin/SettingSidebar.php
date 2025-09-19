<?php

namespace App\View\Components\Sidebar;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingSidebar extends Component
{
    public $settings;  // Array of settings to display in the sidebar
    
    /**
     * Create a new component instance.
     * 
     * @param array $settings Array containing the settings to be displayed in the sidebar
     */
    public function __construct($settings)
    {
        $this->settings = $settings;  // Initialize settings array
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.sidebar.setting-sidebar');  // Render the setting sidebar view
    }
}
