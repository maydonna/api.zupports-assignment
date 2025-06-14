<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


## About Zupports Assignment

API for Zupports assignment recruitment

## Framework & Tools used

- Laravel 12

# Development

### API Documents

Please visit /docs for API documents.

### Requirement

- PHP 8.3

### Installation

1. Clone project (and initialize git flow if using SourceTree [master, develop])
2. Create .env file by copy content from .env.example `cp .env.example .env`
3. Config custom DNS for your machine ([api.zupports-assignment.test](https://api.zupports-assignment.test)), Make sure url
   match `APP_URL` in .env, also
   config `APP_FRONTEND_URL` if you're using custom domain for frontend.
   See [Config Valet](development_docs/config_valet.md) for how to config Valet on Mac.
4. Run `composer install` (for Windows user,
   use `composer install --ignore-platform-req ext-pcntl --ignore-platform-req ext-posix`)
5. Run `php artisan key:generate`
6. Run `php artisan storage:link`
