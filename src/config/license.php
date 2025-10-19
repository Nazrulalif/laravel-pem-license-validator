<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License File Path
    |--------------------------------------------------------------------------
    |
    | The absolute path to your license.pem file.
    | Default: storage/app/license.pem
    |
    */
    'license_path' => env('LICENSE_PATH', storage_path('app/license.pem')),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Enable caching to improve performance by avoiding file reads
    | and signature verification on every request.
    |
    */
    'cache' => [
        'enabled' => env('LICENSE_CACHE_ENABLED', true),
        'ttl' => env('LICENSE_CACHE_TTL', 86400), // 24 hours
        'driver' => env('LICENSE_CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period (Days)
    |--------------------------------------------------------------------------
    |
    | Number of days after expiry where license remains valid (with warning).
    | Set to 0 to disable grace period.
    | Example: 7 = license works for 7 days after expiry
    |
    */
    'grace_period_days' => env('LICENSE_GRACE_PERIOD_DAYS', 0),

    /*
    |--------------------------------------------------------------------------
    | RSA Public Key
    |--------------------------------------------------------------------------
    |
    | The RSA public key used to verify license signatures.
    | This should match the private key used to sign licenses.
    |
    | IMPORTANT: Keep this key secure and do not share the private key.
    |
    */
    'public_key' => env('LICENSE_PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA...
(Your RSA public key here - replace this with your actual key)
-----END PUBLIC KEY-----'),

    /*
    |--------------------------------------------------------------------------
    | Failure Behavior
    |--------------------------------------------------------------------------
    |
    | Configure what happens when license validation fails.
    |
    */
    'on_failure' => [
        'redirect' => env('LICENSE_FAILURE_REDIRECT', '/license-expired'),
        'log' => env('LICENSE_FAILURE_LOG', true),
        'abort_code' => env('LICENSE_FAILURE_ABORT_CODE', 403),
    ],
];
