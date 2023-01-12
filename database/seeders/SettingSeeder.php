<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $settings = new \stdClass;
        $settings->name = 'TMail';
        $settings->version = config('app.version', 7.1);
        $settings->license_key = '';
        $settings->api_keys = [];
        $settings->domains = [];
        $settings->homepage = 0;
        $settings->app_header = '';
        $settings->theme = 'default';
        $settings->fetch_seconds = 20;
        $settings->email_limit = 5;
        $settings->fetch_messages_limit = 15;
        $settings->ads = [
            'one' => '',
            'two' => '',
            'three' => '',
            'four' => '',
            'five' => '',
        ];
        $settings->socials = [];
        $settings->colors = [
            'primary' => '#0155b5',
            'secondary' => '#2fc10a',
            'tertiary' => '#d2ab3e'
        ];
        $settings->imap = [
            'host' => 'localhost',
            'port' => 993,
            'encryption' => 'ssl',
            'validate_cert' => true,
            'username' => 'username',
            'password' => 'password',
            'default_account' => 'default',
            'protocol' => 'imap'
        ];
        $settings->language = 'en';
        $settings->enable_create_from_url = false;
        $settings->forbidden_ids = [
            'admin',
            'catch'
        ];
        $settings->blocked_domains = [];
        $settings->cron_password = str_shuffle('6789abcdefghijklmnopqrstuvwxy');
        $settings->delete = [
            'value' => 1,
            'key' => 'd'
        ];
        $settings->custom = [
            'min' => 3,
            'max' => 15
        ];
        $settings->random = [
            'start' => 0,
            'end' => 0
        ];
        $settings->global = [
            'css' => '',
            'js' => '',
            'header' => '',
            'footer' => ''
        ];
        $settings->cookie = [
            'enable' => true,
            'text' => '<p>By using this website you agree to our <a href="#" target="_blank">Cookie Policy</a></p>'
        ];
        $settings->disable_used_email = false;
        $settings->captcha = 'off'; //Options - off|recaptcha2|recaptcha3|hcaptcha
        $settings->recaptcha2 = [
            'site_key' => '',
            'secret_key' => ''
        ];
        $settings->recaptcha3 = [
            'site_key' => '',
            'secret_key' => ''
        ];
        $settings->hcaptcha = [
            'site_key' => '',
            'secret_key' => ''
        ];
        $settings->after_last_email_delete = 'redirect_to_homepage';
        $settings->date_format = 'd M Y h:i A';
        $settings->groot_theme_options = [
            'extra_text_page' => 0
        ];
        $settings->disable_mailbox_slug = false;
        $settings->enable_masking_external_link = true;
        $settings->add_mail_in_title = true;
        $settings->enable_ad_block_detector = false;

        foreach ($settings as $key => $value) {
            if (!Setting::where('key', $key)->exists()) {
                Setting::create([
                    'key' => $key,
                    'value' => serialize($value)
                ]);
            }
        }

        //START To be removed in v8.0
        $recaptcha = Setting::where('key', 'recaptcha')->first();
        if ($recaptcha) {
            $value = unserialize($recaptcha['value']);
            if ($value['enabled']) {
                $captcha = Setting::where('key', 'captcha')->first();
                $captcha->value = serialize('recaptcha3');
                $captcha->save();
            }
            $recaptcha3 = Setting::where('key', 'recaptcha3')->first();
            $recaptcha3->value = serialize([
                'site_key' => $value['site_key'],
                'secret_key' => $value['secret_key']
            ]);
            $recaptcha->delete();
        }
        //END To be removed in v8.0
    }
}
