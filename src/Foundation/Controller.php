<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Http\Controllers\AdminController;

class Controller extends AdminController
{
    protected function title(): string
    {
        if ($title = $this->customTitle()) {
            return $title;
        }

        $uri = request()->route()->uri();
        $prefix = config('admin.route.prefix');
        $path = substr($uri, strlen($prefix));
        $titles = trans('menu.titles');

        return $titles[$path] ?? parent::title();
    }

    private function customTitle(): ?string
    {
        return null;
    }
}