<?php

namespace Biin2013\DcatAdminTools\Foundation\Show;

use Dcat\Admin\Show\Field as BaseField;

class Field extends BaseField
{
    public function label($style = 'success'): BaseField
    {
        return parent::label($style);
    }
}