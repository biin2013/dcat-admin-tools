<?php

namespace Biin2013\DcatAdminTools;

use Biin2013\DcatAdminTools\Console\Commands\ImportConfig;
use Biin2013\DcatAdminTools\Console\Commands\ImportDb;
use Biin2013\DcatAdminTools\Console\Commands\Menu;
use Dcat\Admin\Admin;
use Illuminate\Support\ServiceProvider;

class DcatAdminToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            ImportConfig::class,
            ImportDb::class,
            Menu::class
        ]);
    }

    public function boot(): void
    {
        Admin::script(file_get_contents('./Asserts/js/common.js'));
    }
}