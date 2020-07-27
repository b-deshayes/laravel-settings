<?php

namespace Kotus\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use Kotus\Settings\Settings;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $root = dirname(__DIR__, 2);

        if (function_exists('config_path')) {
            $this->publishes([
                $root . '/config/settings.php' => config_path('settings.php'),
            ], 'config');
        }

        $this->loadMigrationsFrom($root . '/database/migrations');
    }

    /**
    *  Register any application services.
    *
    * @return void
    */
    public function register(): void
    {
        $this->app->singleton('settings', Settings::class);
    }
}
