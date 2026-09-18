<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class Layer extends AbstractDisplayer
{
    protected string $title = '';
    protected string $icon = '';
    protected string $buttonType = 'primary';
    protected string $layerWidth = '80vw';
    protected string $layerHeight = '80vh';

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function buttonType(string $type): static
    {
        $this->buttonType = $type;

        return $this;
    }

    public function layerWidth(string $width): static
    {
        $this->layerWidth = $width;

        return $this;
    }

    public function layerHeight(string $height): static
    {
        $this->layerHeight = $height;

        return $this;
    }

    public function display($callback = null)
    {
        if (func_num_args() == 2) {
            [$title, $callback] = func_get_args();
        }

        $title = $title ?? ($this->title ?: $this->value);

        $callback = $callback->call($this->row, $this);

        return $this->renderButton($callback->getUrl(), $title);
    }

    protected function renderButton(string $url, string $title = ''): string
    {
        $icon = $this->icon ? "<i class='{$this->icon}'></i>&nbsp;&nbsp;" : '';

        return "<button
                    type='button'
                    class='open-layer btn btn-{$this->buttonType}'
                    data-url='{$url}'
                    data-title='{$title}'
                    data-width='{$this->layerWidth}'
                    data-height='{$this->layerHeight}'
                >
                    {$icon}{$this->value}
                </button>";
    }
}
