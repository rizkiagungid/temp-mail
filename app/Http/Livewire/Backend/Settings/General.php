<?php

namespace App\Http\Livewire\Backend\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class General extends Component {

    use WithFileUploads;

    /**
     * Components State
     */
    public $state = [
        'name' => '',
        'pages' => [],
        'homepage' => 0,
        'app_header' => '',
        'colors' => [
            'primary' => '#000000',
            'secondary' => '#000000',
            'tertiary' => '#000000'
        ],
        'cookie' => [],
        'custom_logo' => '',
        'custom_favicon' => '',
        'language' => 'en',
        'enable_create_from_url' => false,
        'disable_mailbox_slug' => false,
        'enable_masking_external_link' => true,
        'enable_ad_block_detector' => false,
    ];

    public $logo, $favicon;

    public function mount() {
        $pages = Page::where('parent_id', null)->get();
        foreach ($pages as $page) {
            $this->state['pages'][$page->id] = $page->title;
        }
        foreach ($this->state as $key => $value) {
            if (!in_array($key, ['pages'])) {
                $this->state[$key] = config('app.settings.' . $key);
            }
        }
        if (Storage::exists('public/images/custom-logo.png')) {
            $this->state['custom_logo'] = Storage::url('public/images/custom-logo.png');
        }
        if (Storage::exists('public/images/custom-favicon.png')) {
            $this->state['custom_favicon'] = Storage::url('public/images/custom-favicon.png');
        }
    }

    public function update() {
        $this->validate(
            [
                'state.name' => 'required',
                'state.logo' => 'image|max:1024',
                'state.favicon' => 'image|max:1024'
            ],
            [
                'state.name.required' => 'App Name is Required',
                'state.logo.image' => 'Invalid Logo file',
                'state.logo.max' => 'Max Size is 1MB',
                'state.favicon.image' => 'Invalid Logo file',
                'state.favicon.max' => 'Max Size is 1MB'
            ]
        );
        if ($this->logo) {
            $this->logo->storeAs('public/images', 'custom-logo.png');
        }
        if ($this->favicon) {
            $this->favicon->storeAs('public/images', 'custom-favicon.png');
        }
        if ($this->state['homepage'] != 0) {
            $this->state['disable_mailbox_slug'] = false;
        }
        $settings = Setting::whereIn('key', ['name', 'homepage', 'app_header', 'colors', 'cookie', 'language', 'enable_create_from_url', 'disable_mailbox_slug', 'enable_masking_external_link', 'enable_ad_block_detector'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        $this->emit('saved');
    }

    public function render() {
        return view('backend.settings.general');
    }
}
