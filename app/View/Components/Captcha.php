<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Captcha extends Component {

    public $field, $size;

    public function __construct($field, $size = 'normal') {
        $this->field = $field;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render() {
        return view('components.captcha');
    }
}
