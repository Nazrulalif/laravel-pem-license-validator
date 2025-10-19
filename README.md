# Laravel PEM License Validator

Offline license validation for Laravel applications using RSA-4096 signed PEM files.

## Features

- ✅ Offline validation (no internet required)
- ✅ RSA-4096 signature verification
- ✅ Configurable caching
- ✅ Optional grace period
- ✅ Hardware binding support
- ✅ Feature flags (JSON-based)
- ✅ Laravel middleware included
- ✅ Facade support

## Requirements

- PHP 8.1+
- Laravel 9.0+
- OpenSSL extension
- JSON extension

## Installation

```bash
composer require nazrulalif/laravel-pem-license-validator
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Nazrulalif\LaravelPemLicenseValidator\LicenseServiceProvider"
```

This creates `config/license.php`

### Place License File

Place your `license.pem` file in:

```
storage/app/license.pem
```

## Configuration

Edit `config/license.php`:

```php
return [
    'license_path' => storage_path('app/license.pem'),

    'cache' => [
        'enabled' => true,
        'ttl' => 86400, // 24 hours
        'driver' => 'file',
    ],

    'grace_period_days' => 0, // 0=disabled, 7=7 days after expiry

    'public_key' => env('LICENSE_PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----...'),

    'on_failure' => [
        'redirect' => '/license-expired',
        'log' => true,
    ],
];
```

## Usage

### Basic Validation

```php
use Nazrulalif\LaravelPemLicenseValidator\LicenseValidator;

$validator = new LicenseValidator(config('license'));

if ($validator->isValid()) {
    $license = $validator->getLicenseData();
    echo "Valid until: " . $license['expiryDate'];
} else {
    abort(403, 'Invalid license');
}
```

### Using Facade

```php
use Nazrulalif\LaravelPemLicenseValidator\Facades\License;

if (License::isValid()) {
    $daysLeft = License::getDaysRemaining();
    echo "License expires in {$daysLeft} days";
}
```

### Middleware Protection

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    'license' => \Nazrulalif\LaravelPemLicenseValidator\Middleware\ValidateLicense::class,
];
```

Use in routes:

```php
Route::middleware('license')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);
});
```

### Bootstrap Validation

In `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    try {
        $validator = app(\Nazrulalif\LaravelPemLicenseValidator\LicenseValidator::class);

        if (!$validator->isValid()) {
            abort(403, 'Invalid or expired license: ' . $validator->getError());
        }

        // Check grace period
        if ($validator->isInGracePeriod()) {
            logger()->warning('License in grace period. Days remaining: ' . $validator->getDaysRemaining());
        }

    } catch (\Exception $e) {
        abort(500, 'License validation error: ' . $e->getMessage());
    }
}
```

### Feature Checks

```php
use Nazrulalif\LaravelPemLicenseValidator\Facades\License;

// Check if feature exists
if (License::hasFeature('api_access')) {
    // Enable API routes
}

// Get feature value with default
$maxUsers = License::getFeature('UserLimit', 10);

// Get all features
$features = License::getLicenseData()['features'];
```

## API Reference

### LicenseValidator

```php
// Validate license
$validator->validate(): array

// Check if valid
$validator->isValid(): bool

// Get license data
$validator->getLicenseData(): array

// Get days remaining
$validator->getDaysRemaining(): int

// Check grace period
$validator->isInGracePeriod(): bool

// Get feature value
$validator->getFeature(string $key, mixed $default = null): mixed

// Check feature exists
$validator->hasFeature(string $key): bool

// Get error message
$validator->getError(): ?string
```

## License Data Structure

```json
{
  "productKey": "XXXXX-XXXXX-XXXXX-XXXXX-XXXXX",
  "licenseId": "LIC-1234567890",
  "licenseType": "PROFESSIONAL",
  "companyName": "Acme Corporation",
  "email": "customer@example.com",
  "maxDevices": 5,
  "features": {
    "UserLimit": 50,
    "sso": true,
    "api_access": true,
    "storage_gb": 100
  },
  "hardwareId": null,
  "issueDate": "2025-01-01T00:00:00Z",
  "expiryDate": "2026-01-01T00:00:00Z"
}
```

## Exception Handling

```php
use Nazrulalif\LaravelPemLicenseValidator\Exceptions\LicenseException;

try {
    $validator->validate();
} catch (LicenseException $e) {
    switch ($e->getCode()) {
        case LicenseException::FILE_NOT_FOUND:
            // Handle missing file
            break;
        case LicenseException::INVALID_SIGNATURE:
            // Handle tampered license
            break;
        case LicenseException::EXPIRED:
            // Handle expiry
            break;
        case LicenseException::HARDWARE_MISMATCH:
            // Handle hardware binding
            break;
    }
}
```

## Troubleshooting

### License file not found

Ensure `license.pem` exists at the configured path (default: `storage/app/license.pem`)

### Invalid signature

- License file may be corrupted or tampered
- Public key in config doesn't match the key used to sign the license

### Permission denied

```bash
chmod 644 storage/app/license.pem
```

### Cache issues

Clear license cache:

```php
License::clearCache();
```

## Security

- Never commit `license.pem` to version control
- Add to `.gitignore`: `storage/app/license.pem`
- Keep public key secure in `.env` file
- Use HTTPS when transferring license files

## Support

For issues or questions, contact: nazrulism17@gmail.com

## License

MIT License - see LICENSE file
