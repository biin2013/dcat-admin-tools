<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Support\Facades\Cache;

class BaseCache
{
    protected const CACHE_KEY = [];

    public static function clear(): void
    {
        array_map(fn($key) => Cache::forget($key), static::CACHE_KEY);
    }
}