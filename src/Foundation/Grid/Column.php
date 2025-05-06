<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid;

use Biin2013\DcatAdminTools\Foundation\Grid\Displayers\Label;
use Dcat\Admin\Grid\Column as BaseColumn;

class Column extends BaseColumn
{
    public function __construct($name, $label)
    {
        parent::__construct($name, $label);

        self::$displayers['label'] = Label::class;
    }
}