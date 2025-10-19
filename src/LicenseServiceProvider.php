<?php

namespace Nazrulalif\LaravelPemLicenseValidator;

use Illuminate\Support\ServiceProvider;
use Nazrulalif\LaravelPemLicenseValidator\Middleware\ValidateLicense;

class LicenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/license.php',
            'license'
        );

        // Register singleton
        $this->app->singleton(LicenseValidator::class, function ($app) {
            return new LicenseValidator(config('license'));
        });

        // Register alias
        $this->app->alias(LicenseValidator::class, 'license.validator');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/config/license.php' => config_path('license.php'),
        ], 'license-config');

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('license', ValidateLicense::class);
    }

    public function provides(): array
    {
        return [
            LicenseValidator::class,
            'license.validator',
        ];
    }
}
