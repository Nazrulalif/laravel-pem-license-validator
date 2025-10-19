<?php

namespace Nazrulalif\LaravelPemLicenseValidator;

class LicenseCache
{
    private string $cacheKey = 'pem_license_data';
    private array $config;
    private ?array $memoryCache = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        // Return from memory if already loaded
        if ($this->memoryCache !== null) {
            return $this->memoryCache;
        }

        // Try to get from persistent cache
        if ($this->useLaravelCache()) {
            $cached = cache()->get($this->cacheKey);
            if ($cached && $this->isCacheValid($cached)) {
                $this->memoryCache = $cached;
                return $cached;
            }
        } else {
            // Fallback to file-based cache
            $cached = $this->getFromFileCache();
            if ($cached && $this->isCacheValid($cached)) {
                $this->memoryCache = $cached;
                return $cached;
            }
        }

        return null;
    }

    public function put(array $data): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $cacheData = [
            'data' => $data,
            'cached_at' => time(),
        ];

        // Store in memory
        $this->memoryCache = $cacheData;

        // Store in persistent cache
        if ($this->useLaravelCache()) {
            cache()->put($this->cacheKey, $cacheData, $this->getTtl());
        } else {
            $this->putToFileCache($cacheData);
        }
    }

    public function forget(): void
    {
        $this->memoryCache = null;

        if ($this->useLaravelCache()) {
            cache()->forget($this->cacheKey);
        } else {
            $this->deleteFileCache();
        }
    }

    public function has(): bool
    {
        return $this->get() !== null;
    }

    private function isEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    private function getTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 86400; // 24 hours default
    }

    private function isCacheValid(array $cached): bool
    {
        if (!isset($cached['cached_at']) || !isset($cached['data'])) {
            return false;
        }

        $age = time() - $cached['cached_at'];
        return $age < $this->getTtl();
    }

    private function useLaravelCache(): bool
    {
        return function_exists('cache');
    }

    private function getCacheFilePath(): string
    {
        $storageDir = sys_get_temp_dir();

        // Try to use Laravel storage if available
        if (function_exists('storage_path')) {
            $storageDir = storage_path('framework/cache');
        }

        return $storageDir . '/pem_license_cache.json';
    }

    private function getFromFileCache(): ?array
    {
        $path = $this->getCacheFilePath();

        if (!file_exists($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : null;
    }

    private function putToFileCache(array $data): void
    {
        $path = $this->getCacheFilePath();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($path, json_encode($data));
    }

    private function deleteFileCache(): void
    {
        $path = $this->getCacheFilePath();
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
