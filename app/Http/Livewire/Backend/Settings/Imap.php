<?php

namespace App\Http\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Models\TMail;
use Exception;

class Imap extends Component {

    /**
     * Components State
     */
    public $state = [
        'imap' => [
            'host' => '',
            'port' => 993,
            'encryption' => '',
            'validate_cert' => false,
            'username' => '',
            'password' => '',
            'default_account' => 'default',
            'protocol' => 'imap'
        ],
        'error' => null
    ];

    public function mount() {
        $this->state['imap'] = config('app.settings.imap');
    }

    private function test() {
        try {
            TMail::connectMailBox($this->state['imap']);
            return true;
        } catch (Exception $e) {
            $this->state['error'] = $e->getMessage();
        }
    }

    public function update() {
        $this->validate(
            [
                'state.imap.host' => 'required',
                'state.imap.port' => 'required|numeric',
                'state.imap.username' => 'required',
                'state.imap.password' => 'required',
            ],
            [
                'state.imap.host.required' => 'Host field is Required',
                'state.imap.port.required' => 'Port field is Required',
                'state.imap.port.numeric' => 'Port field can only be Numeric',
                'state.imap.username.required' => 'Username field is Required',
                'state.imap.password.required' => 'Password field is Required',
            ]
        );
        $this->state['error'] = null;
        if ($this->test()) {
            $setting = Setting::where('key', 'imap')->first();
            $setting->value = serialize($this->state['imap']);
            $setting->save();
            $this->emit('saved');
        }
    }

    public function render() {
        return view('backend.settings.imap');
    }
}
