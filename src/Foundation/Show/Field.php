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
            $resolveTitle = $title ?? $this->label ?? $value;
            if (!$resolveTitle) return '';

            return Modal::make()
                ->title($resolveTitle)
                ->size($size)
                ->scrollable()
                ->body($content)
                ->button('<button class="btn btn-sm btn-' . $btnType . '" type="button">' .
                    ($this->label ?? $title ?? $value) . '</button>');
        });
    }

    public function layer(
        mixed  $label,
        mixed  $callback,
        string $title = '',
        string $icon = '',
        string $btnType = 'primary',
        string $width = '80vw',
        string $height = '80vh'
    ): Field
    {
        if (!$label) return $this->as('');

        return $this->renderButton(
            $callback->getUrl(),
            $label,
            $title ?: $label,
            $icon,
            $btnType,
            $width,
            $height
        );
    }

    protected function renderButton(
        string $url,
        string $label,
        string $title,
        string $icon,
        string $btnType,
        string $width,
        string $heigh
    ): Field
    {
        $icon = $icon ? "<i class='{$icon}'></i>&nbsp;&nbsp;" : '';

        return $this->unescape()->as(fn() => "<button
                    type='button'
                    class='open-layer btn btn-{$btnType}'
                    data-url='{$url}'
                    data-title='{$title}'
                    data-width='{$width}'
                    data-height='{$heigh}'
                >
                    {$icon}{$label}
                </button>"
        );
    }
}