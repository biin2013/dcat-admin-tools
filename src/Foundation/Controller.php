<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Http\Controllers\AdminController;

class Controller extends AdminController
{
    protected string $modelClass;

    protected function title(): string
    {
        $titles = trans('menu.titles');

        return $titles[$this->getCurrentUri()] ?? parent::title();
    }

    protected function getCurrentUri(): string
    {
        return substr(request()->route()->uri(), strlen(config('admin.route.prefix')));
    }
}