<?php

namespace Kotus\Settings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Settings
{

    public const DEFAULT_TENANT = 'main';

    /**
     * Get the settings
     *
     * @param string $tenant
     * @return array
     */
    private function getSettings(string $tenant = self::DEFAULT_TENANT): array
    {
        return Cache::rememberForever('settings' . $tenant, static function () use ($tenant) {
            return DB::table('settings')
                ->where('tenant', '=', $tenant)
                ->get()
                ->keyBy('name')
                ->toArray();
        });
    }

    /**
     * Return the given key or keys.
     *
     * @param string|array|null $key
     * @param array $options
     * @return array|string
     */
    public function get($key = null, array $options = [])
    {
        $tenant = $options['tenant'] ?? self::DEFAULT_TENANT;
        $settings = $this->getSettings($tenant);

        collect($settings)->map(static function($setting) {
            $setting['value'] = Crypt::decrypt($setting['value']);
            return $setting;
        });

        if ($key === null) {
            return $settings;
        }

        if (is_array($key)) {
            $result = [];
            foreach ($key as $k) {
                $result[] = $settings[$k];
            }
            return $result;
        }

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return [];
    }


    /**
     * Check if a given key exists.
     *
     * @param string $key
     * @param array $options
     * @return boolean
     */
    public function has($key, $options = []): bool
    {
        $tenant = $options['tenant'] ?? self::DEFAULT_TENANT;
        $settings = $this->getSettings($tenant);
        dump($settings);
        return array_key_exists($key, $settings);
    }

    /**
     * Set the value for the given key.
     *
     * @param string $key
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function set($key, $value, $options = []): bool
    {
        $tenant = $options['tenant'] ?? self::DEFAULT_TENANT;

        DB::table('settings')->where([
            ['key', '=', $key],
            ['tenant', '=', $tenant]
        ])->delete();

        DB::table('settings')->insert([
            'key' => $key,
            'value' => Crypt::encrypt($value),
            'tenant' => $tenant
        ]);

        Cache::forget('settings' . $tenant);

        return true;
    }
}
