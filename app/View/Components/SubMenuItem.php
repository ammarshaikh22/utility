<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubMenuItem extends Component
{
    // Public properties to hold text, link, permission, and addon data
    public $text;
    public $link;
    public $permission;
    public $addon;

    /**
     * Create a new component instance.
     *
     * @param string $text - The text for the sub-menu item.
     * @param string $link - The link associated with the sub-menu item.
     * @param bool $permission - The permission flag to determine if the item should be displayed (default is true).
     * @param bool $addon - A flag indicating if an addon is associated with the item (default is false).
     */
    public function __construct($text, $link, $permission = true, $addon = false)
    {
        // Assign the provided text, link, addon, and permission values to the component's properties
        $this->text = $text;
        $this->link = $link;
        $this->addon = $addon;
        // Show icon only when permission is true
        $this->permission = $permission;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string - The view for this component that represents the sub-menu item.
     */
    public function render()
    {
        // Return the view for the 'sub-menu-item' component
        return view('components.sub-menu-item');
    }
}
