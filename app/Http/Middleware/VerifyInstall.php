<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class VerifyInstall {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if (!file_exists(storage_path('installed')) && $request->route()->getName() !== 'installer') {
            Artisan::call('key:generate', ["--force" => true]);
            return redirect()->route('installer');
        }
        return $next($request);
    }
}
