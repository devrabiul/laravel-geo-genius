<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Locale
    |--------------------------------------------------------------------------
    |
    | This value determines the default locale that will be used by the package
    | for translations when auto-detection is not enabled or fails.
    |
    */
    'app_locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Auto Translation
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic translation based on visitor location.
    | If set to true, the package will attempt to detect and apply the
    | visitor's preferred language automatically.
    |
    */
    'translate' => [
        'auto_translate' => env('GEO_AUTO_TRANSLATE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure how long geolocation data should be cached to improve
    | performance. Set in minutes or use null for default caching.
    |
    */
    'cache' => [
        'ttl_minutes' => 10080, // 7 days - cache lifetime in minutes.
    ],

    /*
    |--------------------------------------------------------------------------
    | Phone Input Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for the international phone input field.
    | You can set the default country, placeholder behavior, and format options.
    |
    */
    'phone_input' => [
        'initial_country' => env('GEO_PHONE_DEFAULT_COUNTRY', 'us'),
        'only_countries_mode' => false,
        'only_countries_array' => ['us'],
        'auto_insert_dial_code' => false,
        'national_mode' => false,
        'separate_dial_code' => false,
        'show_selected_dial_code' => true, // (Optional: don't duplicate inside input)
        'auto_placeholder' => 'off',
    ],
];
