<?php

namespace Biin2013\DcatAdminTools\Foundation\Show;

use Dcat\Admin\Admin;
use Dcat\Admin\Show\Field as BaseField;
use Dcat\Admin\Widgets\Modal;

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

    public function modal(
        mixed   $content,
        ?string $title = null,
        string  $btnType = 'primary',
        string  $size = 'lg'
    ): Field
    {
        return $this->unescape()->as(function ($value) use ($content, $title, $btnType, $size) {
            return Modal::make()
                ->title($title ?? $this->label ?? $value)
                ->size($size)
                ->scrollable()
                ->body($content)
                ->button('<button class="btn btn-sm btn-' . $btnType . '" type="button">' .
                    ($this->label ?? $title ?? $value) . '</button>');
        });
    }
}