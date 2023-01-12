<?php

namespace App\Http\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;

class Ads extends Component {

    /**
     * Components State
     */
    public $state = [
        'ads' => [
            'one' => '',
            'two' => '',
            'three' => '',
            'four' => '',
            'five' => ''
        ]
    ];

    public function mount() {
        $this->state['ads'] = config('app.settings.ads');
    }

    public function update() {
        $setting = Setting::where('key', 'ads')->first();
        $setting->value = serialize($this->state['ads']);
        $setting->save();
        $this->emit('saved');
    }

    public function render() {
        return view('backend.settings.ads');
    }
}
