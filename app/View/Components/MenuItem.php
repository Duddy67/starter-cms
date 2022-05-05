<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MenuItem extends Component
{
    public $sub;
    public $href;
    public $icon;
    public $active;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($href, $active, $icon = false, $sub = false)
    {
	$this->sub = $sub;
        $this->href = $href;
        $this->icon = $icon;
        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.menu-item');
    }
}
