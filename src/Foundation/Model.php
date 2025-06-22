<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model as Base;

class Model extends Base
{
    use HasDateTimeFormatter;
}