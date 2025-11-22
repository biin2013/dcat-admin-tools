<?php

namespace Biin2013\DcatAdminTools\Middleware;

use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next)
    {
        if (
            !config('admin.auth.enable', true)
            || !Admin::guard()->guest()
            || $this->shouldPassThrough($request)
        ) {
            return $next($request);
        }

        return redirect()->guest(admin_url('auth/login'));
    }
}