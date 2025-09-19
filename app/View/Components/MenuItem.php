<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuItem extends Component
{
    // Public properties that will be passed to the view for rendering
    public $icon;   // Icon for the menu item (can be a string or HTML for the icon)
    public $text;   // Text label for the menu item
    public $link;   // URL for the menu item (optional)
    public $active; // Boolean to indicate if the menu item is active
    public $addon;  // Boolean to indicate if an addon (like a badge) should be displayed
    public $count;  // Count value (e.g., notifications or items) to display on the menu item

    /**
     * Create a new component instance.
     *
     * @param string $icon  The icon for the menu item (e.g., "home", "settings")
     * @param string $text  The text label for the menu item
     * @param string|null $link  (optional) The URL for the menu item (defaults to null)
     * @param bool $active  (optional) Whether the menu item is active (defaults to false)
     * @param bool $addon   (optional) Whether an addon (badge, etc.) should be displayed (defaults to false)
     * @param int $count    (optional) The count to display with the addon (defaults to 0)
     * @return void
     */
    public function __construct($icon, $text, $link = null, $active = false, $addon = false, $count = 0)
    {
        // Assign values to the public properties
        $this->text = $text;
        $this->icon = $icon;
        $this->link = $link;
        $this->active = $active;
        $this->addon = $addon;
        $this->count = $count;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * This method is responsible for returning the view that will render the component.
     *
     * @return View|string  The view that will be used to render the menu item component
     */
    public function render()
    {
        // Return the view associated with this component (assuming it's in resources/views/components/menu-item.blade.php)
        return view('components.menu-item');
    }
}
