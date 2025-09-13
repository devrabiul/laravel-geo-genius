<?php

use Devrabiul\LaravelGeoGenius\Facades\LaravelGeoGenius;

if (!function_exists('laravelGeoGenius')) {
    /**
     * Get the LaravelGeoGenius core service.
     *
     * This helper provides quick access to Geo, Timezone, Language services.
     * Example usage:
     *
     * ```php
     * laravelGeoGenius()->geo()->getClientIp();
     * laravelGeoGenius()->timezone()->getUserTimezone();
     * laravelGeoGenius()->language()->detect();
     * ```
     *
     * @return \Devrabiul\LaravelGeoGenius\LaravelGeoGenius
     */
    function laravelGeoGenius(): \Devrabiul\LaravelGeoGenius\LaravelGeoGenius
    {
        return app(\Devrabiul\LaravelGeoGenius\LaravelGeoGenius::class);
    }
}
