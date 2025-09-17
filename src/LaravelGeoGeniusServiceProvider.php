<?php

namespace Devrabiul\LaravelGeoGenius;

use Devrabiul\LaravelGeoGenius\Commands\AddNewLanguage;
use Devrabiul\LaravelGeoGenius\Commands\AddTimezoneColumn;
use Devrabiul\LaravelGeoGenius\Commands\GenerateTranslationFiles;
use Devrabiul\LaravelGeoGenius\Commands\TranslateLanguageAll;
use Devrabiul\LaravelGeoGenius\Commands\TranslateLanguageBatch;
use Devrabiul\LaravelGeoGenius\Commands\TranslateLanguage;
use Devrabiul\LaravelGeoGenius\Services\GeoLocationService;
use Devrabiul\LaravelGeoGenius\Services\LanguageService;
use Devrabiul\LaravelGeoGenius\Services\TimezoneService;
use Exception;
use Illuminate\Support\ServiceProvider;
/**
 * Class LaravelGeoGeniusServiceProvider
 *
 * Service provider for the LaravelGeoGenius Laravel package.
 *
 * Handles bootstrapping of the package including:
 * - Setting up asset routes for package resources.
 * - Managing version-based asset publishing.
 * - Configuring processing directory detection.
 * - Registering package publishing commands.
 * - Registering the LaravelGeoGenius singleton.
 *
 * @package Devrabiul\LaravelGeoGenius
 */
class LaravelGeoGeniusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * This method is called after all other services have been registered,
     * allowing you to perform actions like route registration, publishing assets,
     * and configuration adjustments.
     *
     * It:
     * - Sets the system processing directory config value.
     * - Defines a route for serving package assets in development or fallback.
     * - Handles version-based asset publishing, replacing assets if package version changed.
     * - Registers publishable resources when running in console.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->updateProcessingDirectoryConfig();
        $this->app->register(AssetsServiceProvider::class);
        $this->app->register(LaravelTimezoneServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * This method registers:
     * - Configuration file publishing to the application's config directory.
     * - Asset publishing to the public vendor directory, replacing old assets if found.
     *
     * It is typically called when the application is running in console mode
     * to enable artisan vendor:publish commands.
     *
     * @return void
     */
    private function registerPublishing(): void
    {
        // Merge default config so package works without publishing
        $this->mergeConfigFrom(__DIR__ . '/config/laravel-geo-genius.php', 'laravel-geo-genius');

        // Publish config and migration stub
        $this->publishes([
            __DIR__ . '/config/laravel-geo-genius.php' => config_path('laravel-geo-genius.php'),
            __DIR__ . '/database/migrations/add_timezone_column_to_users_table.php.stub' =>
                database_path('migrations/' . date('Y_m_d_His') . '_add_timezone_column_to_users_table.php'),
        ], ['config', 'migrations']);
    }

    /**
     * Register any application services.
     *
     * This method:
     * - Loads the package config file if not already loaded.
     * - Registers a singleton instance of the LaravelGeoGenius class in the Laravel service container.
     *
     * This allows other parts of the application to resolve the 'LaravelGeoGenius' service.
     *
     * @return void
     */
    public function register(): void
    {
        $configPath = config_path('laravel-geo-genius.php');

        if (!file_exists($configPath)) {
            config(['laravel-geo-genius' => require __DIR__ . '/config/laravel-geo-genius.php']);
        }

        $this->commands([
            AddTimezoneColumn::class,
            AddNewLanguage::class,
            GenerateTranslationFiles::class,
            TranslateLanguageAll::class,
            TranslateLanguageBatch::class,
            TranslateLanguage::class,
        ]);

        $this->app->singleton(GeoLocationService::class);
        $this->app->singleton(TimezoneService::class);
        $this->app->singleton(LanguageService::class);

        $this->app->singleton(LaravelGeoGenius::class, function ($app) {
            return new LaravelGeoGenius(
                $app->make(GeoLocationService::class),
                $app->make(TimezoneService::class),
                $app->make(LanguageService::class),
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * This method is used by Laravel deferred providers mechanism
     * and lists the services that this provider registers.
     *
     * @return array<string> Array of service container binding keys provided by this provider.
     */
    public function provides(): array
    {
        return ['LaravelGeoGenius'];
    }

    /**
     * Determine and set the 'system_processing_directory' configuration value.
     *
     * This detects if the current PHP script is being executed from the public directory
     * or the project root directory, or neither, and sets a config value accordingly:
     *
     * - 'public' if script path equals public_path()
     * - 'root' if script path equals base_path()
     * - 'unknown' otherwise
     *
     * This config can be used internally to adapt asset loading or paths.
     *
     * @return void
     */
    private function updateProcessingDirectoryConfig(): void
    {
        $scriptPath = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        $basePath   = realpath(base_path());
        $publicPath = realpath(public_path());

        if ($scriptPath === $publicPath) {
            $systemProcessingDirectory = 'public';
        } elseif ($scriptPath === $basePath) {
            $systemProcessingDirectory = 'root';
        } else {
            $systemProcessingDirectory = 'unknown';
        }

        config(['laravel-geo-genius.system_processing_directory' => $systemProcessingDirectory]);
    }

}
