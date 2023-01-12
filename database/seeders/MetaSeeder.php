<?php

namespace Database\Seeders;

use App\Models\Meta;
use Illuminate\Database\Seeder;

class MetaSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $metas = new \stdClass;
        $metas->email_ids_created = 0;
        $metas->messages_received = 0;
        foreach ($metas as $key => $meta) {
            if (!Meta::where('key', $key)->exists()) {
                Meta::create([
                    'key' => $key,
                    'value' => $meta
                ]);
            }
        }
    }
}
