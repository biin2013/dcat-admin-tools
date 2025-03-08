<?php

namespace Biin2013\DcatAdminTools\Renders;

use Closure;
use ReflectionClass;
use ReflectionException;

class LoadGrid
{
    /**
     * @throws ReflectionException
     */
    public static function make($controllerClass, Closure $filter)
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        $controller = $reflectionClass->newInstance();

        $translation = $reflectionClass->getMethod('resolveTranslation')
            ->invoke($controller, $controllerClass);
        app('admin.translator')->setPath($translation);

        $grid = $reflectionClass->getMethod('grid')->invoke($controller);
        call_user_func($filter, $grid);

        return $grid->disableToolbar()
            ->disableActions()
            ->disableRowSelector()
            ->disablePagination();
    }
}