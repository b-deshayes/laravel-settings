<?php

namespace Kotus\Settings\Tests;

use Illuminate\Foundation\Application;
use Kotus\Settings\Providers\SettingsServiceProvider;
use Kotus\Settings\Settings;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    public const DATABASE_NAME = 'testing';

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    /**
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', self::DATABASE_NAME);
        $app['config']->set('database.connections.' . self::DATABASE_NAME, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->provideDefaultSettingsValue($app);
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            SettingsServiceProvider::class
        ];
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Settings' => Settings::class
        ];
    }

    /**
     * Use to provide default settings key/value from settings file.
     *
     * @param Application $app
     *
     * @return void
     */
    private function provideDefaultSettingsValue($app): void
    {
        $app['config']->set('settings.defaults', [
            'first_key' => 'My first value',
            'other_tenant_key' => [
                'value' => 'Other value from custom tenant',
                'tenant' => 'custom'
            ]
        ]);
    }
}
