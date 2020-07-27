# laravel-settings

![Units tests](https://github.com/Kotus-s/laravel-settings/workflows/Units%20tests/badge.svg?branch=master)

Simple key/value secure settings stored in database

## Installation
Install the package via composer.

```bash
composer require kotus/laravel-settings
```

Publish the config file and fill in the configs. Be sure to provide defaults settings before running the `php artisan migrate` command.

```bash
php artisan vendor:publish --provider="Kotus\Settings\Providers\SettingsServiceProvider" --tag="config"
```

Be sure to run migration to create the settings table.

```bash
php artisan migrate
```

## Usage

You can now use any of the following methods to handle your settings.

```php
 Settings::get($key = null, array $options = [])
 Settings::has(string $key, array $options = []): bool
 Settings::set(string $key, string $value, array $options = []): bool
 Settings::add(string $key, string $value, array $options = []): bool
 Settings::flushCache(array $tenants = []): void
```

At any point, you can pass a specific tenant in option like this:

```php
$value = Settings::get('my_settings', ['tenant' => 'my_tenant']);
```

All settings are cache by tenant category depending on the ttl defined in config file. Default is 30 minutes. Whenever you add or change a setting, the cache is flush.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](https://choosealicense.com/licenses/mit/)
