<?php

namespace Kotus\Settings\Tests\Features;

use Illuminate\Support\Facades\DB;
use Kotus\Settings\Facades\Settings;
use Kotus\Settings\Tests\TestCase;

class TestSettings extends TestCase
{
    /** @test */
    public function set_a_setting(): void
    {
        $this->assertDatabaseMissing('settings', ['key' => 'test']);

        Settings::set('test', 'test');

        $this->assertDatabaseHas('settings', ['key' => 'test']);
    }

    /** @test */
    public function set_a_tenant_setting(): void
    {
        $this->assertDatabaseMissing('settings', [
            'key' => 'test',
            'tenant' => 'test'
        ]);

        Settings::set('test', 'test', ['tenant' => 'test']);

        $this->assertDatabaseHas('settings', [
            'key' => 'test',
            'tenant' => 'test'
        ]);
    }

    /** @test */
    public function has_a_setting(): void
    {
        Settings::set('test', 'test');

        self::assertTrue(Settings::has('test'));
    }
}
