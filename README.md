# Laravel Deploy Doctor

A small Artisan command that checks common Laravel deployment requirements and prints actionable advice.

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

Checks include the application key (its value is never printed), production debug mode, HTTPS `APP_URL` in production, writable `storage` and `bootstrap/cache` directories, the public storage symlink, configuration caching, and accidental `public/.env` exposure.

Warnings do not fail the command by default. Use strict mode in CI/deployment pipelines to make warnings fail:

```bash
php artisan deploy:check --strict
```

For automation, emit JSON instead of a table:

```bash
php artisan deploy:check --json
```

The command returns exit code `1` when a check fails, or when strict mode encounters a warning; otherwise it returns `0`.

## Run package tests

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
