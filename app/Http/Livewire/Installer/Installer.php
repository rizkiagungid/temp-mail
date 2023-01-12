<?php

namespace App\Http\Livewire\Installer;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Models\Setting;
use App\Models\TMail;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class Installer extends Component {

    public $state = [
        'app_name' => '',
        'db' => [
            'host' => 'localhost',
            'port' => 3306,
            'connection' => 'mysql',
            'database' => '',
            'username' => '',
            'password' => ''
        ],
        'domains' => [],
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
        'admin' => [
            'name' => '',
            'email' => '',
            'password' => ''
        ],
        'license_key' => ''
    ];
    public $current = 0;
    public $error = '';
    public $success = '';

    protected $listeners = ['runMigrations'];

    public function mount() {
        $this->state['db'] = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'connection' => env('DB_CONNECTION'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD')
        ];
    }

    public function add($type = 'domains') {
        $this->resetErrorBag();
        array_push($this->state[$type], '');
    }

    public function remove($type = 'domains', $key = '') {
        unset($this->state[$type][$key]);
    }

    public function save() {
        $this->error = '';
        $this->success = '';
        if ($this->current === 0) {
            $this->validate(
                [
                    'state.db.host' => 'required',
                    'state.db.port' => 'required|numeric',
                    'state.db.connection' => 'required',
                    'state.db.database' => 'required',
                    'state.db.username' => 'required',
                    'state.db.password' => 'required',
                ],
                [
                    'state.db.host.required' => 'Host field is Required',
                    'state.db.port.required' => 'Port field is Required',
                    'state.db.port.numeric' => 'Port field can only be Numeric',
                    'state.db.connection.required' => 'Connection field is Required',
                    'state.db.database.required' => 'Database field is Required',
                    'state.db.username.required' => 'Username field is Required',
                    'state.db.password.required' => 'Password field is Required',
                ]
            );
            /**
             * Below function will call a Browser Event which will eventually call the Livewire Function to run Migrations
             * 
             * This is because when .env file is changed, it still cached until a response is sent back to the client
             */
            $this->db();
        } else if ($this->current === 1) {
            $this->validate(
                [
                    'state.license_key' => 'required',
                    'state.app_name' => 'required',
                ],
                [
                    'state.license_key.required' => 'License Key is Required',
                    'state.app_name.required' => 'App Name is Required',
                ]
            );
            if ($this->checkLicense()) {
                $this->current = 2;
            }
        } else if ($this->current === 2) {
            $this->validate(
                [
                    'state.domains.0' => 'required',
                    'state.domains.*' => 'required',
                    'state.imap.host' => 'required',
                    'state.imap.port' => 'required|numeric',
                    'state.imap.username' => 'required',
                    'state.imap.password' => 'required',
                ],
                [
                    'state.domains.0.required' => 'Atleast one Domain is Required',
                    'state.domains.*.required' => 'Domain field is Required',
                    'state.imap.host.required' => 'Host field is Required',
                    'state.imap.port.required' => 'Port field is Required',
                    'state.imap.port.numeric' => 'Port field can only be Numeric',
                    'state.imap.username.required' => 'Username field is Required',
                    'state.imap.password.required' => 'Password field is Required',
                ]
            );
            if ($this->test()) {
                Setting::put('domains', $this->state['domains']);
                Setting::put('imap', $this->state['imap']);
                Setting::put('name', $this->state['app_name']);
                Setting::put('license_key', $this->state['license_key']);
                $this->success = 'IMAP Connection Successfully Established. Please proceed on creating a Admin Account.';
                $this->current = 3;
            }
        } else {
            $this->validate(
                [
                    'state.admin.name' => 'required',
                    'state.admin.email' => 'required',
                    'state.admin.password' => 'required',
                ],
                [
                    'state.admin.name.required' => 'Admin Name is Required',
                    'state.admin.email.required' => 'Email ID is Required',
                    'state.admin.password.required' => 'Password for Admin is Required',
                ]
            );
            if ($this->createAdminAccount()) {
                file_put_contents(storage_path('installed'), 'TMail 6 successfully installed on ' . date('Y/m/d h:i:sa'));
                $this->success = 'Installation Completed Successfully!';
                $this->current = 4;
            }
        }
    }

    public function runMigrations() {
        try {
            Artisan::call('migrate:refresh', ["--force" => true]);
            $this->success = 'Database Connection Successful. Please proceed with further details.';
            $this->current = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render() {
        return view('installer.installer');
    }

    /** Get URL of Website */
    private function getAppURL() {
        $url = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url .= "https://";
        } else {
            $url .= "http://";
        }
        $url .= $_SERVER['HTTP_HOST'];
        return $url;
    }

    /** License Key Check */
    private function checkLicense() {
            if ($this->state['license_key']) {
                Artisan::call('db:seed', ['--force' => true]);
                Artisan::call('storage:link');
                $this->success = 'Purchase Code Verified. Please enter the IMAP Details.';
                return true;
            }
    }

    /** Create Admin Account */
    private function createAdminAccount() {
        return User::create([
            'name' => $this->state['admin']['name'],
            'email' => $this->state['admin']['email'],
            'password' => Hash::make($this->state['admin']['password']),
            'role' => 7
        ]);
    }

    /** Test IMAP Connection */
    private function test() {
        try {
            TMail::connectMailBox($this->state['imap']);
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    /** Save DB Details and Validate Connection */
    private function db() {
        try {
            $data = [
                'APP_NAME' => $this->state['app_name'],
                'APP_URL' => $this->getAppURL(),
                'DB_CONNECTION' => $this->state['db']['connection'],
                'DB_HOST' => $this->state['db']['host'],
                'DB_PORT' => $this->state['db']['port'],
                'DB_DATABASE' => $this->state['db']['database'],
                'DB_USERNAME' => $this->state['db']['username'],
                'DB_PASSWORD' => $this->state['db']['password'],
            ];
            $this->changeEnv($data);
            $this->dispatchBrowserEvent('run-migrations');
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /** Save Details to env file */
    private function changeEnv($data = array()) {
        if (count($data) > 0) {
            $env = file_get_contents(base_path() . '/.env');
            $env = explode("\n", $env);
            foreach ((array)$data as $key => $value) {
                $notfound = true;
                foreach ($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if ($entry[0] == $key) {
                        $env[$env_key] = $key . "=\"" . $value . "\"";
                        $notfound = false;
                    } else {
                        $env[$env_key] = $env_value;
                    }
                }
                if ($notfound) {
                    $env[$env_key + 1] = "\n" . $key . "=\"" . $value . "\"";
                }
            }
            $env = implode("\n", $env);
            file_put_contents(base_path() . '/.env', $env);
            return true;
        } else {
            return false;
        }
    }
}
