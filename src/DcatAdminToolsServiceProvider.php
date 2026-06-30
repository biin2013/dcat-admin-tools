<?php

namespace Biin2013\DcatAdminTools;

use Biin2013\DcatAdminTools\Console\Commands\Import;
use Biin2013\DcatAdminTools\Console\Commands\Menu;
use Illuminate\Support\ServiceProvider;

class DcatAdminToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            Import::class,
            Menu::class
        ]);
    }
}