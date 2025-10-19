<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nazrulalif\LaravelPemLicenseValidator\LicenseValidator;
use Nazrulalif\LaravelPemLicenseValidator\Exceptions\LicenseException;

// Configuration
$config = [
    'license_path' => __DIR__ . '/../storage/app/license.pem',
    'cache' => [
        'enabled' => true,
        'ttl' => 86400,
    ],
    'grace_period_days' => 7,
    'public_key' => '-----BEGIN PUBLIC KEY-----
YOUR_PUBLIC_KEY_HERE
-----END PUBLIC KEY-----',
];

// Create validator
$validator = new LicenseValidator($config);

try {
    // Validate license
    $license = $validator->validate();

    echo "✅ License Valid!\n";
    echo "──────────────────────────────\n";
    echo "Company: {$license['companyName']}\n";
    echo "License Type: {$license['licenseType']}\n";
    echo "Valid Until: {$license['expiryDate']}\n";
    echo "Days Remaining: " . $validator->getDaysRemaining() . "\n";
    echo "Max Devices: {$license['maxDevices']}\n";

    // Check grace period
    if ($validator->isInGracePeriod()) {
        echo "\n⚠️ WARNING: License in grace period!\n";
    }

    // Check features
    echo "\nFeatures:\n";
    foreach ($license['features'] as $key => $value) {
        $display = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
        echo "  • {$key}: {$display}\n";
    }

    // Feature checks
    if ($validator->hasFeature('api_access')) {
        echo "\n🔌 API Access Enabled\n";
    }

    $userLimit = $validator->getFeature('UserLimit', 0);
    echo "👥 User Limit: {$userLimit}\n";

    // Application starts here
    echo "\n🚀 Starting application...\n";
} catch (LicenseException $e) {
    echo "❌ License Validation Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    exit(1);
}
