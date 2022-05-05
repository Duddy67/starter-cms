<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Input extends Component
{
    public $field;
    public $value;


    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($field, $value = null)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.input');
    }
}
