<?php

namespace App\Http\Livewire\Frontend;

use Livewire\Component;

class Page extends Component {

    public $page;

    public function mount($page = '') {
        $this->page = $page;
    }

    public function render() {
        return view('themes.' . config('app.settings.theme') . '.components.page');
    }
}
