<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model as Base;

class Model extends Base
{
    use HasDateTimeFormatter;

    public static string $orderColumn = 'id';
    public static string $orderDirection = 'desc';

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'order',
            fn($query) => $query->orderBy(static::$orderColumn, static::$orderDirection)
        );
    }
}