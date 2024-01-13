<?php

namespace App\View\Components;

use Illuminate\View\Component;

class JsMessages extends Component
{
    public $messages;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Check the Js messages are available.
        $this->messages = (__('messages.js_messages') == 'messages.js_messages') ? '{}' : json_encode(__('messages.js_messages'));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.js-messages');
    }
}
