# laravel-settings

![Units tests](https://github.com/Kotus-s/laravel-settings/workflows/Units%20tests/badge.svg?branch=master)

Simple key/value secure settings stored in database

## Installation
Install the package via composer.

```bash
composer require kotus-s/laravel-settings
```

You can publish the migration and config files, then migrate the new settings table all in one go using:

```bash
php artisan vendor:publish --provider="Kotus\Settings\Providers\SettingsServiceProvider" --tag=migrations && php artisan migrate
```

## Usage
Coming soon ...

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](https://choosealicense.com/licenses/mit/)
