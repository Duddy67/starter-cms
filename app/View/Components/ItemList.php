<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ItemList extends Component
{
    public $columns;
    public $rows;
    public $url;
    public $checkable;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($columns, $rows, $url, $checkable = true)
    {
        $this->columns = $columns;
        $this->rows = $rows;
        $this->url = $url;
        $this->checkable = $checkable;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.item-list');
    }
}
