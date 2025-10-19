Nazrulalif/laravel-pem-license-validator/
│
├── src/ # Main source code
│ ├── LicenseValidator.php # Core validation class (main logic)
│ ├── LicenseCache.php # Caching implementation
│ ├── LicenseServiceProvider.php # Laravel service provider
│ │
│ ├── Exceptions/ # Custom exceptions
│ │ └── LicenseException.php # License validation exceptions
│ │
│ ├── Facades/ # Laravel facades (optional)
│ │ └── License.php # Facade for easy access
│ │
│ ├── Middleware/ # Laravel middleware
│ │ └── ValidateLicense.php # Auto-validate on routes
│ │
│ └── config/ # Package configuration
│ └── license.php # Config file (publishable)
│
├── examples/ # Usage examples
│ ├── basic-usage.php # Plain PHP example
│ ├── laravel-bootstrap.php # Laravel AppServiceProvider
│ ├── laravel-middleware.php # Route middleware example
│ └── feature-check.php # Feature flag examples
│
├── tests/ # PHPUnit tests (optional for v1)
│ ├── LicenseValidatorTest.php
│ └── LicenseCacheTest.php
│
├── .github/ # GitHub specific files
│ └── workflows/
│ └── tests.yml # CI/CD (optional)
│
├── composer.json # Package definition & dependencies
├── README.md # Installation & usage guide
├── LICENSE # MIT License
├── .gitignore # Git ignore rules
└── CHANGELOG.md # Version history (optional)

```

---

## 📂 Detailed Structure with Descriptions
```

Nazrulalif/laravel-pem-license-validator/
│
├── src/  
│ │
│ ├── LicenseValidator.php  
│ │ # Main class - handles all validation logic
│ │ # Methods:
│ │ # - validate()
│ │ # - isValid()
│ │ # - getLicenseData()
│ │ # - getDaysRemaining()
│ │ # - isInGracePeriod()
│ │ # - getFeature()
│ │ # - hasFeature()
│ │
│ ├── LicenseCache.php  
│ │ # Caching layer for performance
│ │ # Methods:
│ │ # - get()
│ │ # - put()
│ │ # - forget()
│ │ # - has()
│ │ # - isExpired()
│ │
│ ├── LicenseServiceProvider.php  
│ │ # Laravel integration
│ │ # Registers:
│ │ # - Config publishing
│ │ # - Singleton binding
│ │ # - Middleware registration
│ │ # - Facade alias
│ │
│ ├── Exceptions/
│ │ └── LicenseException.php  
│ │ # Custom exception with types:
│ │ # - FILE_NOT_FOUND
│ │ # - INVALID_FORMAT
│ │ # - INVALID_SIGNATURE
│ │ # - EXPIRED
│ │ # - HARDWARE_MISMATCH
│ │ # - PARSE_ERROR
│ │
│ ├── Facades/
│ │ └── License.php  
│ │ # Laravel facade for easy access
│ │ # Usage: License::isValid()
│ │
│ ├── Middleware/
│ │ └── ValidateLicense.php  
│ │ # HTTP middleware
│ │ # Auto-validates license on protected routes
│ │
│ └── config/
│ └── license.php  
│ # Configuration file with:
│ # - license_path
│ # - cache settings
│ # - grace_period_days
│ # - public_key
│ # - failure behavior
│
├── examples/
│ │
│ ├── basic-usage.php  
│ │ # Standalone PHP usage (no framework)
│ │ # Shows: manual validation, error handling
│ │
│ ├── laravel-bootstrap.php  
│ │ # app/Providers/AppServiceProvider.php example
│ │ # Shows: validation on app boot
│ │
│ ├── laravel-middleware.php  
│ │ # routes/web.php example
│ │ # Shows: protect routes with middleware
│ │
│ └── feature-check.php  
│ # Feature flag examples
│ # Shows: hasFeature(), getFeature()
│
├── tests/  
│ ├── LicenseValidatorTest.php # Unit tests for validator
│ └── LicenseCacheTest.php # Unit tests for cache
│
├── composer.json  
│ # Package metadata:
│ # - name: Nazrulalif/laravel-pem-license-validator
│ # - autoload: PSR-4 mapping
│ # - dependencies: PHP 8.1+
│ # - laravel provider registration
│
├── README.md  
│ # Documentation:
│ # - Installation steps
│ # - Configuration guide
│ # - Usage examples
│ # - API reference
│ # - Troubleshooting
│
├── LICENSE  
│ # MIT License text
│
├── .gitignore  
│ # Ignore:
│ # - vendor/
│ # - composer.lock
│ # - .DS_Store
│ # - .idea/
│
└── CHANGELOG.md

# Version history # - v1.0.0 - Initial release

```

---

## 🎯 File Sizes Estimate

| File | Lines | Purpose |
|------|-------|---------|
| `LicenseValidator.php` | ~400 | Core validation logic |
| `LicenseCache.php` | ~120 | Caching helper |
| `LicenseException.php` | ~60 | Exception handling |
| `LicenseServiceProvider.php` | ~80 | Laravel integration |
| `ValidateLicense.php` | ~50 | Middleware |
| `License.php` (Facade) | ~20 | Facade accessor |
| `config/license.php` | ~60 | Configuration |
| `composer.json` | ~40 | Package definition |
| `README.md` | ~200 | Documentation |
| **Total** | **~1,030 lines** | **Complete package** |

---

## 📦 After Installation (Customer's Laravel App)
```

customer-laravel-app/
│
├── config/
│ └── license.php # Published config
│ (copied from vendor package)
│
├── storage/
│ └── app/
│ └── license.pem # Customer's license file
│ (provided by you - the license issuer)
│
├── vendor/
│ └── Nazrulalif/
│ └── laravel-pem-license-validator/ # Your package
│
├── app/
│ ├── Http/
│ │ └── Kernel.php  
│ │ # Register middleware:
│ │ # 'license' => \Nazrulalif\...\ValidateLicense::class
│ │
│ └── Providers/
│ └── AppServiceProvider.php  
│ # Optional: validate on boot
│ # License::validate()
│
└── routes/
└── web.php

# Use middleware: # Route::middleware('license')->group(...)
