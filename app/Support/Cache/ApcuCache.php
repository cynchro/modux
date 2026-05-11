<?php

namespace App\Support\Cache;

use App\Support\Contracts\CacheInterface;

class ApcuCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        if (!function_exists('apcu_fetch')) {
            return null;
        }
        $success = false;
        $value   = apcu_fetch($key, $success);
        return $success ? $value : null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        if (function_exists('apcu_store')) {
            apcu_store($key, $value, $ttl);
        }
    }

    public function delete(string $key): void
    {
        if (function_exists('apcu_delete')) {
            apcu_delete($key);
        }
    }

    public function has(string $key): bool
    {
        if (!function_exists('apcu_fetch')) {
            return false;
        }
        $success = false;
        apcu_fetch($key, $success);
        return $success;
    }
}
