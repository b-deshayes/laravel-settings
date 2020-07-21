<?php

namespace Kotus\Settings\Tests\Features;

use Illuminate\Support\Facades\Cache;
use Kotus\Settings\Facades\Settings;
use Kotus\Settings\Tests\TestCase;

class TestSettings extends TestCase
{
    public const DB_SETTINGS_TABLE_NAME = 'settings';

    private $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->makeTestData();
    }

    /**
     * Make a bunch of test data.
     */
    private function makeTestData(): void
    {
        $this->data = [
            'key' => 'test',
            'another_key' => 'another_test',
            'sensitive' => 'data'
        ];

        foreach ($this->data as $k => $v)
        {
            Settings::set($k, $v);
        }
    }

    /** @test */
    public function set_a_setting(): void
    {
        Settings::set('test', 'test');
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, ['key' => 'test']);
    }

    /** @test */
    public function set_a_tenant_setting(): void
    {
        $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'test',
            'tenant' => 'test'
        ]);

        Settings::set('test', 'test', ['tenant' => 'test']);

        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'test',
            'tenant' => 'test'
        ]);
    }

    /** @test */
    public function has_a_setting(): void
    {
        foreach ($this->data as $k => $v)
        {
            self::assertTrue(Settings::has($k));
        }
    }

    /** @test */
    public function get_a_setting(): void
    {
        Settings::set('test', 'test');
        $test = Settings::get('test');
        self::assertSame($test, 'test');
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
           'key' => 'test'
        ]);
    }

    /** @test */
    public function get_all_key_if_null(): void
    {
        $settings = Settings::get();
        foreach ($this->data as $k => $v)
        {
            self::assertArrayHasKey($k, $settings);
            $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
                'key' => $k
            ]);
        }
    }

    /** @test */
    public function get_bunch_of_keys(): void
    {
        $map = ['sensitive', 'another_key'];
        $settings = Settings::get($map);
        foreach ($map as $key)
        {
            self::assertArrayHasKey($key, $settings);
            self::assertSame($settings[$key], $this->data[$key]);
        }
    }

    /** @test */
    public function empty_array_if_not_existing_key(): void
    {
        $settings = Settings::get('invalid_key');
        self::assertIsArray($settings);
        self::assertEmpty($settings);
    }

    /** @test */
    public function value_are_encrypted(): void
    {
        foreach ($this->data as $k => $v)
        {
            $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
                'value' => $v
            ]);
        }
    }

    /** @test */
    public function values_are_cached(): void
    {
        $tenant = 'new_tenant';
        $setting = Settings::get('test', ['tenant' => $tenant]);
        self::assertTrue(Cache::has('settings.' . $tenant));
    }
}
