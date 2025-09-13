<?php

namespace Devrabiul\LaravelGeoGenius\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoLocationService
{
    public function isInternetAvailable(): bool
    {
        try {
            Http::timeout(2)->get('https://github.com/devrabiul'); // https://www.google.com
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getClientIp()
    {
        $geoIPSessionKey = 'visitor_geolocation_ip_for_' . str_replace('.', '_', request()->ip());

        $config = config('laravel-geo-genius', []);
        $cacheConfig = $config['cache'] ?? [];

        return Cache::remember($geoIPSessionKey, ($cacheConfig['ttl_minutes'] ?? now()->addDays(7)), function () {
            if (request()->ip() === '::1' || request()->ip() === '127.0.0.1') {
                try {
                    $response = Http::timeout(3)->get('https://api.ipify.org?format=json');
                    return $response->successful() ? ($response->json()['ip'] ?? '127.0.0.1') : '127.0.0.1';
                } catch (Exception $e) {
                    return '127.0.0.1';
                }
            }

            return request()->server('HTTP_X_FORWARDED_FOR')
                ? explode(',', request()->server('HTTP_X_FORWARDED_FOR'))[0]
                : request()->server('REMOTE_ADDR');
        });
    }

    public function locateVisitor(): array
    {
        $config = config('laravel-geo-genius', []);
        $cacheConfig = $config['cache'] ?? [];

        $ip = $this->getClientIp();
        $geoLocationSessionKey = 'visitor_geolocation_for_' . str_replace('.', '_', $ip);

        if ($this->isInternetAvailable()) {
            return (array) Cache::remember($geoLocationSessionKey, ($cacheConfig['ttl_minutes'] ?? now()->addDays(7)), function () use ($ip) {
                try {
                    $response = Http::timeout(3)->get("https://ipwho.is/$ip");
                    $geoData = self::formatIpWhoResponse($response);
                } catch (Exception $e) {
                    $response = Http::timeout(3)->get("http://ip-api.com/json/$ip");
                    $geoData = self::formatIpApiResponse($response);
                }

                if (empty($geoData) || ($geoData['success'] ?? false) === false) {
                    return [
                        'ip' => $ip,
                        'success' => false,
                        'city' => 'Unknown',
                        'region' => 'Unknown',
                        'country' => 'Unknown',
                        'country_code' => '',
                        'latitude' => 0,
                        'longitude' => 0,
                        'timezone' => null,
                        'currency_code' => null,
                    ];
                }

                return [
                    'ip' => $geoData['ip'] ?? $ip,
                    'success' => $geoData['success'] ?? true,
                    'city' => $geoData['city'] ?? null,
                    'region' => $geoData['region'] ?? null,
                    'country' => $geoData['country'] ?? null,
                    'countryCode' => $geoData['country_code'] ?? ($geoData['countryCode'] ?? null),
                    'country_flag' => $geoData['country_flag'] ?? null,
                    'latitude' => $geoData['latitude'] ?? null,
                    'longitude' => $geoData['longitude'] ?? null,
                    'timezone' => $geoData['timezone'] ?? null,
                    'currency_code' => $geoData['currency_code'] ?? null,
                ];
            });
        }

        return $this->locateVisitorSkeleton();
    }

    public function locateVisitorSkeleton(): array
    {
        return [
            'ip' => $this->getClientIp(),
            'success' => false,
            'city' => 'Unknown',
            'region' => 'Unknown',
            'country' => 'Unknown',
            'country_code' => 'XX',
            'latitude' => 0,
            'longitude' => 0,
            'timezone' => null,
            'currency_code' => null,
        ];
    }

    public function formatIpWhoResponse($response): ?array
    {
        $geoData = $response->successful() ? $response->json() : [];
        return [
            'ip' => $geoData['ip'] ?? '',
            'success' => $geoData['success'] ?? false,
            'city' => $geoData['city'] ?? 'Unknown',
            'region' => $geoData['region'] ?? 'Unknown',
            'country' => $geoData['country'] ?? 'Unknown',
            'country_code' => $geoData['country_code'] ?? '',
            'country_flag' => $geoData['flag']['emoji'] ?? '',
            'latitude' => $geoData['latitude'] ?? 0,
            'longitude' => $geoData['longitude'] ?? 0,
            'timezone' => $geoData['timezone']['id'] ?? null,
            'isp' => $geoData['connection']['isp'] ?? null,
            'org' => $geoData['connection']['org'] ?? null,
            'currency_code' => $geoData['ip'] ?? null,
        ];
    }

    public function formatIpApiResponse($response): ?array
    {
        $geoData = $response->successful() ? $response->json() : [];

        return [
            "ip" => "103.205.134.15",
            "status" => $geoData['status'] ?? false,
            "city" => $geoData['city'] ?? 'Unknown',
            'region' => $geoData['regionName'] ?? 'Unknown',
            "country" => $geoData['country'] ?? 'Unknown',
            "country_code" => $geoData['countryCode'] ?? 'Unknown',
            'country_flag' => $geoData['flag']['emoji'] ?? '',
            'latitude' => $geoData['lat'] ?? '',
            'longitude' => $geoData['lat'] ?? '',
            'timezone' => $geoData['timezone'] ?? null,
            'isp' => $geoData['isp'] ?? null,
            'org' => $geoData['org'] ?? null,
            'currency_code' => null,
        ];
    }

    public function getCountry(): ?string
    {
        $geoData = $this->locateVisitor();
        return $geoData['country'] ?? null;
    }

    public function getCountryCode(): ?string
    {
        $geoData = $this->locateVisitor();
        return $geoData['countryCode'] ?? null;
    }

    public function getCity(): ?string
    {
        $geoData = $this->locateVisitor();
        return $geoData['city'] ?? null;
    }

    public function getRegion(): ?string
    {
        $geoData = $this->locateVisitor();
        return $geoData['region'] ?? null;
    }

    public function getTimezone(): ?string
    {
        $geoData = $this->locateVisitor();
        return $geoData['timezone'] ?? null;
    }

    public function getLatitude(): ?float
    {
        $geoData = $this->locateVisitor();
        return $geoData['latitude'] ?? null;
    }

    public function getLongitude(): ?float
    {
        $geoData = $this->locateVisitor();
        return $geoData['longitude'] ?? null;
    }
}
