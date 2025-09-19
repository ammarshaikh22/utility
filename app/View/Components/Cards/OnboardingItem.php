<?php

namespace App\View\Components\Cards;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OnboardingItem extends Component
{
    public $image;
    public $title;
    public $description;
    
    /**
     * Create a new component instance.
     * 
     * @param string $image Image for the onboarding item
     * @param string $title Title for the onboarding item
     * @param string $description Description for the onboarding item
     */
    public function __construct($image, $title, $description)
    {
        $this->image = $image;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Get the view / contents that represent the component.
     * 
     * @return View|string
     */
    public function render()
    {
        return view('components.cards.onboarding-item');  // Render the view for the onboarding item
    }
}
