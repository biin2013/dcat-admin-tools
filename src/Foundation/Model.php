<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Database\Eloquent\Model as Base;

class Model extends Base
{
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s'); // 去掉 TZ 的格式
    }
}