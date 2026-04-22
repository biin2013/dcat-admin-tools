<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Support\Str;

class Options
{
    private static function resolveLangPath(): string
    {
        $path = array_slice(explode('\\', static::class), 3);
        $end = array_pop($path);

        if (ends_with($end, 'Options')) {
            $end = substr($end, 0, -7);
            $path[] = $end;
        }

        array_walk($path, fn(&$item) => $item = Str::snake($item));

        return implode('/', $path);
    }

    protected static function trans(string $path)
    {
        return trans('options/' . self::resolveLangPath() . '.' . $path);
    }

    protected static function response(array $data, $key = null, $default = null)
    {
        if (is_array($key)) {
            return array_intersect_key($data, array_flip($key));
        }

        return $key === null ? $data : ($data[$key] ?? $default);
    }
}