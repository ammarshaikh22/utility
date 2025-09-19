<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingSidebar extends Component
{
    // Public property to hold the active menu for the sidebar
    public $activeMenu;

    /**
     * Create a new component instance.
     *
     * @param string $activeMenu - The menu item that should be marked as active in the sidebar.
     */
    public function __construct($activeMenu)
    {
        // Assign the provided active menu to the component's property
        $this->activeMenu = $activeMenu;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the setting sidebar.
     */
    public function render()
    {
        // Return the view for the 'setting-sidebar' component
        return view('components.setting-sidebar');
    }
}
