<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminConfig
{
    public static string $table = 'admin_configs';

    private static function resolveCacheKey(string $group): string
    {
        return 'admin_config_' . $group . '_cache';
    }

    private static function resolveFormatCacheKey(string $group): string
    {
        return 'admin_config_' . $group . '_format_cache';
    }

    public static function getValue(string $group, string $key = null)
    {
        return $key
            ? self::get($group)[$key]->value ?? null
            : array_column(self::get($group), 'value', 'key');
    }

    public static function get(string $group, string $key = null)
    {
        $cacheKey = self::resolveFormatCacheKey($group);

        if (Cache::has($cacheKey)) {
            $configs = Cache::get($cacheKey);
        } else {
            $configs = Cache::rememberForever(
                $cacheKey,
                function () use ($group) {
                    $origin = self::getOrigin($group);
                    array_walk(
                        $origin,
                        fn(&$item) => $item->value = self::formatResponse($item->type, $item->value)
                    );
                    return $origin;
                }
            );
        }

        return $key ? $configs[$key] ?? null : $configs;
    }

    public static function getOrigin(string $group, string $key = null)
    {
        $cacheKey = self::resolveCacheKey($group);

        if (Cache::has($cacheKey)) {
            $configs = Cache::get($cacheKey);
        } else {
            $configs = Cache::rememberForever(
                $cacheKey,
                fn() => DB::table(self::$table)->where('group', $group)->get()->pluck(null, 'key')->toArray()
            );
        }

        return $key ? $configs[$key] ?? null : $configs;
    }

    public static function getKeys(string $group): array
    {
        return array_keys(self::getOrigin($group));
    }

    public static function getOriginValue(string $group, string $key = null)
    {
        return $key
            ? self::getOrigin($group)[$key]->value ?? null
            : array_column(self::getOrigin($group), 'value', 'key');
    }

    private static function formatResponse(string $type, $value)
    {
        return match ($type) {
            'bool' => (bool)$value,
            'int' => (int)$value,
            'float' => (float)$value,
            'array' => $value ? explode(',', $value) : [],
            'json' => json_decode($value ?: '[]', true),
            default => $value,
        };
    }

    /**
     * @throws Exception
     */
    public static function set(string $group, string $key, mixed $value): void
    {
        $type = DB::table(self::$table)
            ->where('group', $group)
            ->where('key', $key)
            ->value('type');

        if (!$type) {
            throw new Exception('config group[' . $group . '] key[' . $key . '] not found');
        }

        DB::table(self::$table)
            ->where('group', $group)
            ->where('key', $key)
            ->update([
                'value' => self::formatSave($type, $value)
            ]);
        self::clearCache($group);
    }

    /**
     * @throws Throwable
     */
    public static function setMany(string $group, array $values): void
    {
        DB::transaction(function () use ($group, $values) {
            foreach ($values as $key => $value) {
                self::set($group, $key, $value);
            }
        });
    }

    private static function formatSave(string $type, $value)
    {
        return match ($type) {
            'bool' => $value ? '1' : '0',
            'int' => (int)$value,
            'float' => (float)$value,
            'array' => is_array($value) ? implode(',', $value) : $value,
            'json' => json_encode($value ?: []),
            default => (string)$value,
        };
    }

    public static function clearCache(array|string $group): void
    {
        if (is_array($group)) {
            foreach ($group as $g) {
                self::clearCache($g);
            }
            return;
        }

        Cache::forget(self::resolveCacheKey($group));
        Cache::forget(self::resolveFormatCacheKey($group));
    }

    public static function clearAllCache(): void
    {
        DB::table(self::$table)->distinct()->pluck('group')->each(
            fn($group) => self::clearCache($group)
        );
    }
}