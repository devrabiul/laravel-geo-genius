<?php

namespace Devrabiul\LaravelGeoGenius\Listeners\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Events\AccessTokenCreated;

class UpdateUsersTimezone
{
    public function handle($event): void
    {
        // Handle Passport token creation
        if ($event instanceof AccessTokenCreated) {
            Auth::loginUsingId($event->userId);
            return;
        }

        // Handle normal login
        if ($event instanceof Login) {
            $user = Auth::user();
        } else {
            return;
        }

        if (! $user) {
            return;
        }

        $ip = $this->getFromLookup() ?? request()->ip();

        $geoipInfo = geoip()->getLocation($ip);
        $detectedTimezone = $geoipInfo['timezone'] ?? ($geoipInfo->time_zone['name'] ?? null);

        if ($detectedTimezone && ($user->timezone !== $detectedTimezone)) {
            if (config('timezone.overwrite', false) || is_null($user->timezone)) {
                $user->timezone = $detectedTimezone;
                $user->save();
            }
        }
    }

    private function getFromLookup(): ?string
    {
        foreach (config('timezone.lookup', []) as $type => $keys) {
            if (empty($keys)) {
                continue;
            }
            if ($value = $this->lookup($type, $keys)) {
                return $value;
            }
        }
        return null;
    }

    private function lookup(string $type, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (request()->$type->has($key)) {
                return request()->$type->get($key);
            }
        }
        return null;
    }
}
