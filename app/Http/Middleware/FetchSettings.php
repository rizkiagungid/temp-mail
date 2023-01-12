<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class FetchSettings {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        $options = file_exists(storage_path('installed')) ? Setting::get() : [];
        foreach ($options as $option) {
            config([
                'app.settings.' . $option->key => unserialize($option->value)
            ]);
        }
        return $next($request);
    }
}
