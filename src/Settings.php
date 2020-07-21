<?php

namespace Kotus\Settings;

use Illuminate\Support\Collection;
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
     * @return Collection
     */
    private function getSettings(string $tenant = self::DEFAULT_TENANT): Collection
    {
        return Cache::rememberForever('settings.' . $tenant, static function () use ($tenant) {
            return DB::table('settings')
                ->where('tenant', '=', $tenant)
                ->get()
                ->keyBy('key');
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

        $settings->map(static function($setting) {
            $setting->value = Crypt::decrypt($setting->value);
            return $setting;
        });

        if ($key === null) {
            return $settings->toArray();
        }

        if (is_array($key)) {
            return $settings->filter(static function($v, $k) use ($key) {
                return in_array($k, $key, true);
            })->pluck('value', 'key');
        }

        if ($settings->has($key)) {
            return $settings->get($key)->value;
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
        return $settings->has($key);
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
