<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    use HasFactory;

    public static function pick($key) {
        $setting = Setting::where('key', $key)->first();
        if ($setting) {
            return unserialize($setting->value);
        }
        return false;
    }

    public static function put($key, $value) {
        $setting = Setting::where('key', $key)->first();
        if ($setting) {
            $setting->value = serialize($value);
            return $setting->save();
        }
        return false;
    }
}
