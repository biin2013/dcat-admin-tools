<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid\Displayers;

use Closure;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class Layer extends AbstractDisplayer
{
    protected string $title;

    public function display($callback = null)
    {
        $title = $this->value ?: $this->trans('title');
        if (func_num_args() == 2) {
            [$title, $callback] = func_get_args();
        }

        $title = $this->title ?: $title;
        $html = $this->value;

        if ($callback instanceof Closure) {

        }
    }
}