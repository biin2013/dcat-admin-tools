<?php

namespace Biin2013\DcatAdminTools;

use Biin2013\DcatAdminTools\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider;

class AdminToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['router']->aliasMiddleware('admin.auth', Authenticate::class);
    }
}