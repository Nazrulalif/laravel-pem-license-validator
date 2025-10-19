<?php

namespace Nazrulalif\LaravelPemLicenseValidator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array validate()
 * @method static bool isValid()
 * @method static array|null getLicenseData()
 * @method static int getDaysRemaining()
 * @method static bool isInGracePeriod()
 * @method static mixed getFeature(string $key, mixed $default = null)
 * @method static bool hasFeature(string $key)
 * @method static string|null getError()
 * @method static void clearCache()
 *
 * @see \Nazrulalif\LaravelPemLicenseValidator\LicenseValidator
 */
class License extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'license.validator';
    }
}
