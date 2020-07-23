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
        $collection->each(static function ($item, $key) use ($collection) {
            Settings::add($key, $item, [
                'flush' => $collection->keys()->last() === $key
            ]);
        });
    }

    public function testSetASetting(): void
    {
        Settings::set('key', 'test');
        self::assertSame(Settings::get('key'), 'test');
    }

    public function testAddATenantSetting(): void
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

    public function testHasASetting(): void
    {
        foreach ($this->data as $k => $v) {
            self::assertTrue(Settings::has($k));
        }
    }

    public function testGetASetting(): void
    {
        Settings::set('sensitive', 'test');
        $test = Settings::get('sensitive');
        self::assertSame($test, 'test');
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
           'key' => 'sensitive'
        ]);
    }

    public function testGetAllKeyIfNull(): void
    {
        $settings = Settings::get();
        foreach ($this->data as $k => $v) {
            self::assertArrayHasKey($k, $settings);
            $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
                'key' => $k
            ]);
        }
    }

    public function testGetBunchOfKeys(): void
    {
        $map = ['sensitive', 'another_key'];
        $settings = Settings::get($map);
        foreach ($map as $key) {
            self::assertArrayHasKey($key, $settings);
            self::assertSame($settings[$key], $this->data[$key]);
        }
    }

    public function testEmptyArrayIfNotExistingKey(): void
    {
        $settings = Settings::get('invalid_key');
        self::assertIsArray($settings);
        self::assertEmpty($settings);
    }

    public function testValuesAreEncrypted(): void
    {
        foreach ($this->data as $k => $v) {
            $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
                'value' => $v
            ]);
        }
    }

    public function testValuesAreCached(): void
    {
        $tenant = 'new_tenant';
        Settings::get('test', ['tenant' => $tenant]);
        self::assertTrue(Cache::has('settings.' . $tenant));
    }

    public function testSetNotExistingKey(): void
    {
        self::assertFalse(Settings::set('not_existing_key', 'value'));
        $this->assertDatabaseMissing(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'not_existing_key'
        ]);
    }

    public function testAddExistingKey(): void
    {
        self::assertFalse(Settings::add('key', 'value'));
        $this->assertDatabaseHas(self::DB_SETTINGS_TABLE_NAME, [
            'key' => 'key'
        ]);
    }
}
