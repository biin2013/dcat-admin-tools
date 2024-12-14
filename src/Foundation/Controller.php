<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Trait\UseValidate;
use Dcat\Admin\Http\Controllers\AdminController;

class Controller extends AdminController
{
    use UseValidate;

    protected string $modelClass;

    protected function title(): string
    {
        $titles = trans('menu.titles');

        return $titles[$this->getCurrentUri()] ?? parent::title();
    }

    protected function model()
    {
        return new $this->modelClass;
    }

    protected function getCurrentUri(): string
    {
        return substr(request()->route()->uri(), strlen(config('admin.route.prefix')));
    }

    protected function translation(): string
    {
        if ($this->translation) {
            return $this->translation;
        }

        $path = array_slice(explode('\\', get_class($this)), 3);
        $controller = substr(array_pop($path), 0, -10);
        $path[] = $controller;

        return strtolower(implode('/', $path));
    }
}