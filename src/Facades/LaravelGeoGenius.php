<?php

namespace Devrabiul\LaravelGeoGenius\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGeoGenius extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravelGeoGenius';
    }
}
