<?php

namespace Biin2013\DcatAdminTools\Utils;

class Helper
{
    /**
     * @param string|null $method
     * @return array|string
     */
    public static function resolvePermissionHttpPath(?string $method = null): array|string
    {
        $actionMethod = request()->route()->getActionMethod();
        $method = $method ?? $actionMethod;
        $routeName = request()->route()->getName();

        return substr_replace(
            $routeName,
            static::resolvePermissionActionName($method),
            strlen($routeName) - strlen($actionMethod)
        );
    }

    /**
     * @param string $method
     * @return string
     */
    public static function resolvePermissionActionName(string $method): string
    {
        return match ($method) {
            'show' => 'index',
            'edit' => 'update',
            'create' => 'store',
            default => $method,
        };
    }

    /**
     * @param int $number
     * @return string
     */
    public static function numberToLetter(int $number): string
    {
        if ($number < 1) return '';

        $letter = '';
        while ($number > 0) {
            $number--;
            $letter = chr(65 + $number % 26) . $letter;
            $number = intval($number / 26);
        }

        return $letter;
    }

    /**
     * @param string $letter
     * @return int
     */
    public static function letterToNumber(string $letter): int
    {
        $number = 0;
        $length = strlen($$letter);

        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + ord($letter[$i]) - 64;
        }

        return $number;
    }
}