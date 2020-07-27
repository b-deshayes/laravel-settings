<?php

namespace Kotus\Settings\Database\Seeder;

use Illuminate\Database\Seeder;
use Kotus\Settings\Facades\Settings;

class SettingSeeder extends Seeder
{
    /**
     * Seeding the settings table with the defaults key/value written in the config file.
     */
    public function run(): void
    {
        collect(config('settings.defaults', []))->each(static function($value, $key) {
            $tenant = is_array($value) && !empty($value['tenant']) ? $value['tenant'] : config('settings.default_tenant', 'main');
            $value = is_array($value) && !empty($value['value']) ? $value['value'] : $value;
            Settings::add($key, $value, [
                'tenant' => $tenant
            ]);
        });
    }
}
