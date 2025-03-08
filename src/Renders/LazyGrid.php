<?php

namespace Biin2013\DcatAdminTools\Renders;

use Dcat\Admin\Support\LazyRenderable;
use ReflectionException;

class LazyGrid extends LazyRenderable
{
    /**
     * @throws ReflectionException
     */
    public function render()
    {
        return LoadGrid::make($this->payload['class'], function ($grid) {
            return $grid->model()->where($this->payload['where'] ?? []);
        });
    }
}