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
}