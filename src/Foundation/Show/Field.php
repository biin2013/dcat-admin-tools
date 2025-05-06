<?php

namespace Biin2013\DcatAdminTools\Foundation\Show;

use Dcat\Admin\Admin;
use Dcat\Admin\Show\Field as BaseField;

class Field extends BaseField
{
    public function label($style = 'success'): BaseField
    {
        return parent::label($style);
    }

    public function formatStyle($style): array
    {
        $class = 'default';
        $background = '';

        if ($style !== 'default') {
            $class = '';

            $style = Admin::color()->get($style, $style);
            $background = "style='background:{$style};cursor:default'";
        }

        return [$class, $background];
    }
}