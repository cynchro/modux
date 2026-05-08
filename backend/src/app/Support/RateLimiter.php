<?php

namespace App\Support;

class RateLimiter
{
    public function tooManyAttempts(string $key, int $maxAttempts = 5): bool
    {
        if (!function_exists('apcu_fetch')) {
            return false;
        }
        $attempts = apcu_fetch($key);
        return $attempts !== false && $attempts >= $maxAttempts;
    }

    public function hit(string $key, int $ttlSeconds = 300): void
    {
        if (!function_exists('apcu_fetch')) {
            return;
        }
        $attempts = apcu_fetch($key);
        apcu_store($key, ($attempts === false ? 0 : $attempts) + 1, $ttlSeconds);
    }

    public function clear(string $key): void
    {
        if (function_exists('apcu_delete')) {
            apcu_delete($key);
        }
    }
}
