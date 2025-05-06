<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\Label as BaseLabel;

class Label extends BaseLabel
{
    public function display($style = 'success', $max = 3)
    {
        if (!$value = $this->value($max)) {
            return;
        }

        $original = $this->column->getOriginal();
        $defaultStyle = is_array($style) ? ($style['default'] ?? 'success') : 'success';

        $background = $this->formatStyle(
            is_array($style) ?
                (is_scalar($original) ? ($style[$original] ?? $defaultStyle) : current($style))
                : $style
        );

        return collect($value)->map(function ($name) use ($background) {
            return "<span class='{$this->baseClass}' {$background}>$name</span>";
        })->implode(' ');
    }

    protected function formatStyle($style): string
    {
        $background = 'style="background:#d2d6de;color: #555"';

        if ($style !== 'default') {
            $style = Admin::color()->get($style, $style);

            $background = "style='background:{$style};cursor:default'";
        }

        return $background;
    }
}