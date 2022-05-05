<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public $button;
    // Default classes and icons.
    public $btnClasses = [
        'create' => 'btn-success', 
	'save' => 'btn-success', 
	'saveClose' => 'btn-primary', 
	'destroy' => 'btn-danger', 
	'massDestroy' => 'btn-danger', 
	'batch' => 'btn-primary',
	'checkin' => 'btn-info',
	'massUpdate' => 'btn-primary'
    ];

    public $btnIcons = [
        'create' => 'fa-plus', 
	'save' => 'fa-save', 
	'saveClose' => 'fa-reply', 
	'cancel' => 'fa-times', 
	'destroy' => 'fa-trash', 
	'massDestroy' => 'fa-trash', 
	'batch' => 'fa-copy',
	'checkin' => 'fa-check-circle',
	'publish' => 'fa-check-square',
	'unpublish' => 'fa-circle-blank',
	'massUpdate' => 'fa-sync'
    ];


    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($button)
    {
        $this->button = $button;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.button');
    }
}
