<?php

namespace Nazrulalif\LaravelPemLicenseValidator\Exceptions;

use Exception;

class LicenseException extends Exception
{
    public const FILE_NOT_FOUND = 1001;
    public const INVALID_FORMAT = 1002;
    public const INVALID_SIGNATURE = 1003;
    public const EXPIRED = 1004;
    public const HARDWARE_MISMATCH = 1005;
    public const PARSE_ERROR = 1006;
    public const INVALID_PUBLIC_KEY = 1007;
    public const PRODUCT_KEY_MISMATCH = 1008;

    public static function fileNotFound(string $path): self
    {
        return new self("License file not found: {$path}", self::FILE_NOT_FOUND);
    }

    public static function invalidFormat(string $reason = ''): self
    {
        $message = "Invalid license format";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message, self::INVALID_FORMAT);
    }

    public static function invalidSignature(): self
    {
        return new self("Invalid license signature. License may be tampered or corrupted.", self::INVALID_SIGNATURE);
    }

    public static function expired(string $expiryDate): self
    {
        return new self("License expired on {$expiryDate}", self::EXPIRED);
    }

    public static function hardwareMismatch(string $expected, string $actual): self
    {
        return new self(
            "Hardware ID mismatch. Expected: {$expected}, Actual: {$actual}",
            self::HARDWARE_MISMATCH
        );
    }

    public static function parseError(string $reason): self
    {
        return new self("Failed to parse license: {$reason}", self::PARSE_ERROR);
    }

    public static function invalidPublicKey(): self
    {
        return new self("Invalid or missing public key configuration", self::INVALID_PUBLIC_KEY);
    }

    public static function productKeyMismatch(string $expected, string $actual): self
    {
        return new self(
            "Product key mismatch. Expected: {$expected}, Actual: {$actual}",
            self::PRODUCT_KEY_MISMATCH
        );
    }
}
