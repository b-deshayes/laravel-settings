<?php

namespace Kotus\Settings;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Settings
{

    /**
     * Get and cached the settings.
     *
     * @param string $tenant
     * @return Collection
     */
    private function getSettings(string $tenant = null): Collection
    {
        $tenant = $tenant ?? (string)config('settings.default_tenant', 'main');
        $ttl = now()->addMinutes(config('settings.cache_ttl'));
        $key = 'settings.' . $tenant;

        return Cache::remember($key, $ttl, static function () use ($tenant) {
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
        $tenant = $options['tenant'] ?? config('settings.default_tenant', 'main');
        $settings = $this->getSettings($tenant);

        $settings->map(static function ($setting) {
            $setting->value = Crypt::decrypt($setting->value);
            return $setting;
        });

        if ($key === null) {
            return $settings->toArray();
        }

        if (is_array($key)) {
            return $settings->filter(static function ($v, $k) use ($key) {
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
    public function has(string $key, array $options = []): bool
    {
        $tenant = $options['tenant'] ?? config('settings.default_tenant', 'main');
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
    public function set(string $key, string $value, array $options = []): bool
    {
        $tenant = $options['tenant'] ?? config('settings.default_tenant', 'main');

        if (!$this->has($key, ['tenant' => $tenant])) {
            return false;
        }

        DB::table('settings')->where([
            ['key', '=', $key],
            ['tenant', '=', $tenant]
        ])->update([
            'value' => Crypt::encrypt($value)
        ]);

        $this->flushCache([$tenant]);

        return true;
    }

    /**
     * Add a new key/value pair setting.
     *
     * @param string $key
     * @param string $value
     * @param array $options
     * @return bool
     */
    public function add(string $key, string $value, array $options = []): bool
    {
        $tenant = $options['tenant'] ?? config('settings.default_tenant', 'main');
        $flush = $options['flush'] ?? true;

        if ($this->has($key, ['tenant' => $tenant])) {
            return false;
        }

        DB::table('settings')->insert([
            'key' => $key,
            'value' => Crypt::encrypt($value),
            'tenant' => $tenant
        ]);

        if ($flush) {
            $this->flushCache([$tenant]);
        }

        return true;
    }

    /**
     * Allow to flush full cached settings or specifics tenants.
     *
     * @param array $tenants
     */
    public function flushCache(array $tenants = []): void
    {
        DB::table('settings')
            ->select('tenant')
            ->distinct()
            ->get('tenant')
            ->each(static function ($item) use ($tenants) {
                if (in_array($item->tenant, $tenants, true) || count($tenants) === 0) {
                    Cache::forget('settings.' . $item->tenant);
                }
            });
    }
}
