<?php

namespace Devrabiul\LaravelGeoGenius;

use Devrabiul\LaravelGeoGenius\Services\GeoLocationService;
use Devrabiul\LaravelGeoGenius\Services\TimezoneService;
use Devrabiul\LaravelGeoGenius\Services\LanguageService;
use Illuminate\Support\Facades\File;

/**
 * @property-read GeoLocationService $geo
 * @property-read TimezoneService $timezone
 * @property-read LanguageService $language
 */
class LaravelGeoGenius
{
    /**
     * @var GeoLocationService
     */
    protected GeoLocationService $geo;

    /**
     * @var TimezoneService
     */
    protected TimezoneService $timezone;

    /**
     * @var LanguageService
     */
    protected LanguageService $language;

    /**
     * LaravelGeoGenius constructor.
     */
    public function __construct()
    {
        $this->geo = new GeoLocationService();
        $this->timezone = new TimezoneService();
        $this->language = new LanguageService();
    }

    /**
     * Get the GeoLocation service instance.
     *
     * @return GeoLocationService
     */
    public function geo(): GeoLocationService
    {
        return $this->geo;
    }

    /**
     * Get the Timezone service instance.
     *
     * @return TimezoneService
     */
    public function timezone(): TimezoneService
    {
        return $this->timezone;
    }

    /**
     * Get the Language detection service instance.
     *
     * @return LanguageService
     */
    public function language(): LanguageService
    {
        return $this->language;
    }

    public function initIntlPhoneInput(): string
    {
        $scripts = [];

        $config = config('laravel-geo-genius', []);
        $phoneInputConfig = $config['phone_input'] ?? [
            'initial_country' => env('GEO_PHONE_DEFAULT_COUNTRY', 'us'),
            'auto_insert_dial_code' => false,
            'national_mode' => false,
            'separate_dial_code' => false,
            'show_selected_dial_code' => true,
            'auto_placeholder' => 'off',
        ];

        $phoneInputConfig['detected_country'] = strtolower(laravelGeoGenius()->geo()->getCountryCode());
        $attr = '';
        foreach ($phoneInputConfig as $key => $value) {
            $attr .= ' data-' . str_replace('_', '-', $key) . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $scripts[] = '<span class="system-default-country-code" ' . $attr . '></span>';

        $file1 = 'packages/devrabiul/laravel-geo-genius/plugins/intl-tel-input/css/intlTelInput.css';
        if (File::exists(public_path($file1))) {
            $scripts[] = '<link rel="stylesheet" href="' . $this->getDynamicAsset($file1) . '">';
        }

        $file1 = 'packages/devrabiul/laravel-geo-genius/plugins/intl-tel-input/js/intlTelInput.js';
        $file2 = 'packages/devrabiul/laravel-geo-genius/plugins/intl-tel-input/js/utils.js';
        $file3 = 'packages/devrabiul/laravel-geo-genius/plugins/intl-tel-input/js/intlTelInout-validation.js';


        if (File::exists(public_path($file1)) && File::exists(public_path($file2)) && File::exists(public_path($file3))) {
            $scripts[] = '<script src="' . $this->getDynamicAsset($file1) . '"></script>';
            $scripts[] = '<script src="' . $this->getDynamicAsset($file2) . '"></script>';
            $scripts[] = '<script src="' . $this->getDynamicAsset($file3) . '"></script>';
        }
        return implode('', $scripts);
    }

    private function getDynamicAsset(string $path): string
    {
        if (config('laravel-geo-genius.system_processing_directory') == 'public') {
            $position = strpos($path, 'public/');
            $result = $path;
            if ($position === 0) {
                $result = preg_replace('/public/', '', $path, 1);
            }
        } else {
            $result = in_array(request()->ip(), ['127.0.0.1']) ? $path : 'public/' . $path;
        }

        return asset($result);
    }

}
