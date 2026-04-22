<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model as Base;

class Model extends Base
{
    use HasDateTimeFormatter;

    public static string $scopeOrderColumn = 'id';
    public static string $scopeOrderDirection = 'desc';

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'order',
            fn($query) => $query->orderBy(static::$scopeOrderColumn, static::$scopeOrderDirection)
        );
    }
}