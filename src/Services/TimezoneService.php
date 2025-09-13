<?php

namespace Devrabiul\LaravelGeoGenius\Services;

use DateTime;
use DateTimeZone;
use Exception;

class TimezoneService
{
    /**
     * Detect the current user’s timezone.
     * 1. Use stored timezone on the authenticated user if available.
     * 2. Otherwise, detect by IP (GeoGenius).
     */
    public function getUserTimezone(): string
    {
        if ($user = auth()->user()) {
            if (!empty($user?->timezone)) {
                return $user?->timezone ?? '';
            }
        }

        $geoData = laravelGeoGenius()->geo()->locateVisitor();
        return $geoData['timezone'] ?? 'UTC';
    }

    /**
     * Convert a UTC datetime string to the current user’s timezone.
     * @throws Exception
     */
    public function convertToUserTimezone(string $datetime, ?string $format = 'Y-m-d H:i:s'): string
    {
        $timezone = $this->getUserTimezone();
        try {
            return (new DateTime($datetime, new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone($timezone))
                ->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }

    /**
     * Convert datetime between two timezones.
     * @throws Exception
     */
    public function convertToTimezone(string $from, string $to, string $datetime, ?string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $dt = new DateTime($datetime, new DateTimeZone($from));
            return $dt->setTimezone(new DateTimeZone($to))->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }

    /**
     * List all PHP supported timezone identifiers.
     */
    public function getAllTimezones(): array
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Get UTC offset (like +06:00) for a timezone.
     * @throws Exception
     */
    public function getOffset(string $timezone): string
    {
        try {
            return (new DateTime('now', new DateTimeZone($timezone)))->format('P');
        } catch (Exception $e) {
            return '+00:00';
        }
    }

    /**
     * Get current time in a specific timezone.
     * @throws Exception
     */
    public function getCurrentTimeByTimezone(string $timezone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            return (new DateTime('now', new DateTimeZone($timezone)))->format($format);
        } catch (Exception $e) {
            return now()->format($format);
        }
    }
}
