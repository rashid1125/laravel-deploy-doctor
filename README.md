# Laravel Deploy Doctor

A small Artisan command that checks common Laravel deployment requirements.

## Requirements

- PHP 8.1 or later
- Laravel 10, 11, or 12

## Install

```bash
composer require rashid1125/laravel-deploy-doctor
```

Laravel package discovery registers the service provider automatically.

## Run

```bash
php artisan deploy:check
```

The command checks that `APP_KEY` is configured, production debug mode is off, Laravel's `storage` and `bootstrap/cache` directories are writable, and the `public/storage` symlink exists. A missing symlink is reported as a warning. Failed checks return a non-zero exit code.

## Run package tests

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
