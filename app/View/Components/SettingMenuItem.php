<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SettingMenuItem extends Component
{
    // Public properties to hold the href, text, active state, and menu data
    public $href;
    public $text;
    public $active;
    public $menu;

    /**
     * Create a new component instance.
     *
     * @param string $href - The URL or link for the setting menu item.
     * @param string $text - The text to be displayed for the setting menu item.
     * @param string $menu - The menu to which this item belongs.
     * @param bool $active - A flag indicating if the item is active (default is false).
     */
    public function __construct($href, $text, $menu, $active = false)
    {
        // Assign the provided href, text, active state, and menu values to the component's properties
        $this->text = $text;
        $this->href = $href;
        $this->active = $active;
        $this->menu = $menu;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the setting menu item.
     */
    public function render()
    {
        // Return the view for the 'setting-menu-item' component
        return view('components.setting-menu-item');
    }

    /**
     * This method checks if the current option is active.
     *
     * @param string $option - The menu option to check.
     * @return bool - Returns true if the option is active, false otherwise.
     */
    public function isActive($option)
    {
        // Compare the provided option with the current active state
        return $option === $this->active;
    }
}
