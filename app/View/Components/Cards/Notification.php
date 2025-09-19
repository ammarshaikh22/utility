<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Notification extends Component
{
    public $link;
    public $image;
    public $title;
    public $text;
    public $time;
    public $notification;
    public $type;

    /**
     * Create a new component instance.
     *
     * @param string $link The link for the notification.
     * @param string $image The image for the notification.
     * @param string $title The title of the notification.
     * @param string $time The time when the notification was sent.
     * @param string $notification The notification content.
     * @param string|null $text Additional text for the notification (optional).
     * @param string $type Type of notification, e.g., 'image'.
     */
    public function __construct($link, $image, $title, $time, $notification, $text = null, $type = 'image')
    {
        $this->link = $link;
        $this->image = $image;
        $this->title = $title;
        $this->text = $text;
        $this->time = $time;
        $this->notification = $notification;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.notification');  // Render the notification view
    }
}
