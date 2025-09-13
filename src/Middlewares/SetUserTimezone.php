<?php

namespace Devrabiul\LaravelGeoGenius\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use DateTimeZone;

class SetUserTimezone
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->timezone) {
            try {
                date_default_timezone_set(Auth::user()->timezone);
            } catch (\Throwable $e) {
                // fallback to UTC
                date_default_timezone_set('UTC');
            }
        }

        return $next($request);
    }
}
