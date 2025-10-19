<?php

// File: app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Nazrulalif\LaravelPemLicenseValidator\Facades\License;
use Nazrulalif\LaravelPemLicenseValidator\Exceptions\LicenseException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Validate license on application boot
        try {
            if (!License::isValid()) {
                abort(403, 'Invalid or expired license: ' . License::getError());
            }

            // Log grace period warning
            if (License::isInGracePeriod()) {
                $days = License::getDaysRemaining();
                logger()->warning("License in grace period. Days remaining: {$days}");
            }

            // Log license info (optional)
            $license = License::getLicenseData();
            logger()->info('License validated', [
                'company' => $license['companyName'],
                'type' => $license['licenseType'],
                'expires' => $license['expiryDate'],
            ]);
        } catch (LicenseException $e) {
            logger()->error('License validation failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            abort(500, 'License validation error: ' . $e->getMessage());
        }
    }
}
