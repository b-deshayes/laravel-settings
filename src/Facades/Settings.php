<?php

namespace Kotus\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Settings
 * @package Kotus\Settings\Facades
 *
 * @method static get($key = null, array $options = [])
 * @method static has(string $key, array $options = []): bool
 * @method static set(string $key, string $value, array $options = []): bool
 * @method static add(string $key, string $value, array $options = []): bool
 * @method static flushCache(array $tenants = []): void
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
