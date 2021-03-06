<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check()) {
            $expiresAt = Carbon::now()->addMinutes(15);
            \Cache::put('user-online-'.\Auth::user()->id, true, $expiresAt);
        }
        return $next($request);
    }
}
