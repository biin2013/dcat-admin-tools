<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid\Displayers;

use Closure;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use Dcat\Admin\Support\LazyRenderable;

class Layer extends AbstractDisplayer
{
    public function display($callback = null)
    {
        $attributes = null;
        if (func_num_args() == 2) {
            [$callback, $attributes] = func_get_args();
        }

        $url = $callback;
        if ($callback instanceof Closure) {
            $url = $callback->call($this->row, $this);

            if ($url instanceof LazyRenderable) {
                $url = $url->getUrl();
            }
        }

        if ($attributes instanceof Closure) {
            $attributes = $attributes->call($this->row, $this);
        }
        $attributes = array_merge($this->defaultAttributes(), $attributes ?? []);

        return $this->renderButton(
            $url,
            $attributes['title'],
            $attributes['label'],
            $attributes['icon'],
            $attributes['btn'],
            $attributes['width'],
            $attributes['height']
        );
    }

    protected function defaultAttributes(): array
    {
        return [
            'title' => $this->value ?? '',
            'label' => $this->value ?? '',
            'icon' => '',
            'btn' => 'primary',
            'width' => '80vw',
            'height' => 'auto'
        ];
    }

    protected function renderButton(
        string $url,
        string $title,
        string $label,
        string $icon,
        string $btnType,
        string $width,
        string $height
    ): string
    {
        $icon = $icon ? "<i class='{$icon}'></i>" : '';
        $text = implode('&nbsp;&nbsp;', array_filter([$icon, $label]));

        return "<button
                    type='button'
                    class='open-layer btn btn-{$btnType}'
                    data-url='{$url}'
                    data-title='{$title}'
                    data-width='{$width}'
                    data-height='{$height}'
                >
                    {$text}
                </button>";
    }
}
