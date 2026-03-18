<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    /**
     * Create a new component instance.
     */
    public $id;
    public $title;
    public $centered;

    public function __construct($id, $title = '', $centered = true)
    {
        $this->id = $id;
        $this->title = $title;
        $this->centered = $centered;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal');
    }
}
