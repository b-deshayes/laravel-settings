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
            'key' => 'the_key_value',
            'another_key' => 'another_test',
            'sensitive' => 'data'
        ];

        $collection = collect($this->data);
        $collection->each(static function($item, $key) use ($collection) {
            Settings::add($key, $item, [
                'flush' => $collection->keys()->last() === $key
            ]);
        });
    }

    /** @test */
    public function set_a_setting(): void
    {
        Settings::set('key', 'test');
        self::assertSame(Settings::get('key'), 'test');
    }

    /** @test */
    public function add_a_tenant_setting(): void
    {
        $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'test',
            'tenant' => 'test'
        ]);

        Settings::add('test', 'test', ['tenant' => 'test']);

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
        Settings::set('sensitive', 'test');
        $test = Settings::get('sensitive');
        self::assertSame($test, 'test');
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
           'key' => 'sensitive'
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

    /** @test */
    public function set_not_existing_key(): void
    {
        self::assertFalse(Settings::set('not_existing_key', 'value'));
        $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'not_existing_key'
        ]);
    }

    /** @test */
    public function add_an_existing_key(): void
    {
        self::assertFalse(Settings::add('key', 'value'));
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'key'
        ]);
    }
}
