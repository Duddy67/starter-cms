<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Filters extends Component
{
    public $filters;
    public $url;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($filters, $url)
    {
        $this->filters = $filters;
        $this->url = $url;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.filters');
    }
}
