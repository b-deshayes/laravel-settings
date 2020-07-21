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
        $this->loadMigrationsFrom(dirname(__DIR__, 2) . '/database/migrations');
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
