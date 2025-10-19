<?php

namespace Nazrulalif\LaravelPemLicenseValidator;

use Nazrulalif\LaravelPemLicenseValidator\Exceptions\LicenseException;

class LicenseValidator
{
    private array $config;
    private ?array $licenseData = null;
    private ?string $error = null;
    private LicenseCache $cache;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->cache = new LicenseCache($config);
    }

    public function validate(): array
    {
        // Check cache first
        $cached = $this->cache->get();
        if ($cached !== null && isset($cached['data'])) {
            $this->licenseData = $cached['data'];
            return $this->licenseData;
        }

        try {
            // Read license file
            $pemContent = $this->readLicenseFile();

            // Parse PEM format
            $licenseJson = $this->extractLicenseData($pemContent);
            $signature = $this->extractSignature($pemContent);

            // Decode license data
            $this->licenseData = json_decode($licenseJson, true);
            if (!$this->licenseData) {
                throw LicenseException::parseError('Invalid JSON format');
            }

            // Verify signature
            $this->verifySignature($licenseJson, $signature);

            // Check expiry
            $this->checkExpiry();

            // Check hardware binding
            $this->checkHardwareBinding();

            // Cache the result
            $this->cache->put($this->licenseData);

            return $this->licenseData;
        } catch (LicenseException $e) {
            $this->error = $e->getMessage();
            throw $e;
        }
    }

    public function isValid(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (LicenseException $e) {
            return false;
        }
    }

    public function getLicenseData(): ?array
    {
        if ($this->licenseData === null) {
            try {
                $this->validate();
            } catch (LicenseException $e) {
                return null;
            }
        }
        return $this->licenseData;
    }

    public function getDaysRemaining(): int
    {
        $data = $this->getLicenseData();
        if (!$data || !isset($data['expiryDate'])) {
            return 0;
        }

        $expiryTimestamp = strtotime($data['expiryDate']);
        $daysRemaining = floor(($expiryTimestamp - time()) / 86400);

        return max(0, (int) $daysRemaining);
    }

    public function isInGracePeriod(): bool
    {
        $gracePeriodDays = $this->config['grace_period_days'] ?? 0;

        if ($gracePeriodDays <= 0) {
            return false;
        }

        $data = $this->getLicenseData();
        if (!$data || !isset($data['expiryDate'])) {
            return false;
        }

        $expiryTimestamp = strtotime($data['expiryDate']);
        $now = time();

        // Check if expired but within grace period
        if ($expiryTimestamp < $now) {
            $daysSinceExpiry = floor(($now - $expiryTimestamp) / 86400);
            return $daysSinceExpiry <= $gracePeriodDays;
        }

        return false;
    }

    public function getFeature(string $key, mixed $default = null): mixed
    {
        $data = $this->getLicenseData();
        if (!$data || !isset($data['features'])) {
            return $default;
        }

        return $data['features'][$key] ?? $default;
    }

    public function hasFeature(string $key): bool
    {
        $data = $this->getLicenseData();
        if (!$data || !isset($data['features'])) {
            return false;
        }

        return isset($data['features'][$key]);
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function clearCache(): void
    {
        $this->cache->forget();
        $this->licenseData = null;
    }

    private function readLicenseFile(): string
    {
        $path = $this->config['license_path'];

        if (!file_exists($path)) {
            throw LicenseException::fileNotFound($path);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw LicenseException::fileNotFound($path);
        }

        return $content;
    }

    private function extractLicenseData(string $pemContent): string
    {
        if (!preg_match('/-----BEGIN LICENSE-----(.*?)-----END LICENSE-----/s', $pemContent, $matches)) {
            throw LicenseException::invalidFormat('LICENSE section not found');
        }

        $base64Data = trim($matches[1]);
        $decoded = base64_decode($base64Data, true);

        if ($decoded === false) {
            throw LicenseException::invalidFormat('Invalid base64 encoding');
        }

        return $decoded;
    }

    private function extractSignature(string $pemContent): string
    {
        if (!preg_match('/-----BEGIN LICENSE SIGNATURE-----(.*?)-----END LICENSE SIGNATURE-----/s', $pemContent, $matches)) {
            throw LicenseException::invalidFormat('SIGNATURE section not found');
        }

        $base64Signature = trim($matches[1]);
        $decoded = base64_decode($base64Signature, true);

        if ($decoded === false) {
            throw LicenseException::invalidFormat('Invalid signature encoding');
        }

        return $decoded;
    }

    private function verifySignature(string $data, string $signature): void
    {
        $publicKey = $this->config['public_key'] ?? '';

        if (empty($publicKey)) {
            throw LicenseException::invalidPublicKey();
        }

        $key = openssl_pkey_get_public($publicKey);
        if ($key === false) {
            throw LicenseException::invalidPublicKey();
        }

        $verified = openssl_verify($data, $signature, $key, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw LicenseException::invalidSignature();
        }
    }

    private function checkExpiry(): void
    {
        if (!isset($this->licenseData['expiryDate'])) {
            throw LicenseException::parseError('Missing expiry date');
        }

        $expiryTimestamp = strtotime($this->licenseData['expiryDate']);
        $gracePeriodDays = $this->config['grace_period_days'] ?? 0;

        // If grace period is enabled
        if ($gracePeriodDays > 0) {
            $graceEndTimestamp = $expiryTimestamp + ($gracePeriodDays * 86400);

            if (time() > $graceEndTimestamp) {
                throw LicenseException::expired($this->licenseData['expiryDate']);
            }
        } else {
            // No grace period
            if ($expiryTimestamp < time()) {
                throw LicenseException::expired($this->licenseData['expiryDate']);
            }
        }
    }

    private function checkHardwareBinding(): void
    {
        if (empty($this->licenseData['hardwareId'])) {
            return; // No hardware binding
        }

        $expectedHardwareId = $this->licenseData['hardwareId'];
        $currentHardwareId = $this->getHardwareId();

        if ($expectedHardwareId !== $currentHardwareId) {
            throw LicenseException::hardwareMismatch($expectedHardwareId, $currentHardwareId);
        }
    }

    private function getHardwareId(): string
    {
        // Get MAC address (first non-loopback interface)
        if (PHP_OS_FAMILY === 'Windows') {
            exec('getmac /NH /FO CSV', $output);
            if (!empty($output[0])) {
                // Parse CSV output
                $parts = str_getcsv($output[0]);
                return strtoupper(str_replace('-', ':', trim($parts[0])));
            }
        } else {
            // Linux/Unix
            $interfaces = ['eth0', 'ens33', 'enp0s3', 'wlan0'];

            foreach ($interfaces as $interface) {
                $path = "/sys/class/net/{$interface}/address";
                if (file_exists($path)) {
                    $mac = trim(file_get_contents($path));
                    if ($mac && $mac !== '00:00:00:00:00:00') {
                        return strtoupper($mac);
                    }
                }
            }

            // Fallback: use ip command
            exec('ip link show | grep -m 1 "link/ether" | awk \'{print $2}\'', $output);
            if (!empty($output[0])) {
                return strtoupper(trim($output[0]));
            }
        }

        return '';
    }
}
