<?php

namespace Devrabiul\LaravelGeoGenius;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use JamesMills\LaravelTimezone\Listeners\Auth\UpdateUsersTimezone;

class LaravelTimezoneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services after registration.
     *
     * Publishes migration to add timezone column if it doesn't exist,
     * and registers event listeners for updating user's timezone on login and token creation.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish migration if the migration class doesn't exist
        if (! class_exists('AddTimezoneColumnToUsersTable')) {
            $this->publishes([
                __DIR__ . '/database/migrations/add_timezone_column_to_users_table.php.stub' =>
                    database_path('migrations/' . date('Y_m_d_His') . '_add_timezone_column_to_users_table.php'),
            ], 'migrations');
        }

        $this->registerEventListener();
    }

    /**
     * Register bindings and merge config.
     *
     * @return void
     */
    public function register(): void
    {
        // 
    }

    /**
     * Register event listeners related to timezone updates.
     *
     * @return void
     */
    private function registerEventListener(): void
    {
        $events = [
            \Illuminate\Auth\Events\Login::class,
            \Laravel\Passport\Events\AccessTokenCreated::class,
        ];

        // Event::listen($events, UpdateUsersTimezone::class);
    }
}
