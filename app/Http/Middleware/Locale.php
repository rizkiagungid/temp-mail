<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Locale {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        try {
            $locale = explode('-', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0])[0];
            if (in_array($locale, config('app.locales'))) {
                session(['browser-locale' => $locale]);
            }
        } catch (\Exception $e) {
        }
        app()->setLocale(session('locale', session('browser-locale', config('app.settings.language', config('app.locale', 'en')))));
        return $next($request);
    }
}
