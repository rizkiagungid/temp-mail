<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model {
    use HasFactory;

    public function incrementMeta($value = 1) {
        $this->value = $this->value + $value;
        $this->save();
        return true;
    }

    public static function incrementEmailIdsCreated($value = 1) {
        $meta = Meta::where('key', 'email_ids_created')->first();
        if ($meta) {
            $meta->incrementMeta($value);
            return true;
        }
        return false;
    }

    public static function incrementMessagesReceived($value = 1) {
        $meta = Meta::where('key', 'messages_received')->first();
        if ($meta) {
            $meta->incrementMeta($value);
            return true;
        }
        return false;
    }

    public static function getEmailIdsCreated() {
        $meta = Meta::where('key', 'email_ids_created')->first();
        if ($meta) {
            return $meta->value;
        }
        return "NaN";
    }

    public static function getMessagesReceived() {
        $meta = Meta::where('key', 'messages_received')->first();
        if ($meta) {
            return $meta->value;
        }
        return "NaN";
    }
}
