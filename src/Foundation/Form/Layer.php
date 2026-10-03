<?php

namespace Biin2013\DcatAdminTools\Foundation\Form;

use Dcat\Admin\Form\Field;

class Layer extends Field
{
    protected string $url;
    protected string $text = '';
    protected string $title = '';
    protected string $icon = '';
    protected string $btnType = 'primary';
    protected string $layerWidth = '80vw';
    protected string $layerHeight = 'auto';

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }

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

    public function btnType(string $type): static
    {
        $this->btnType = $type;

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

    public function render(): string
    {
        $variables = $this->variables();
        $viewClass = $variables['viewClass'];
        return <<<HTML
            <div class="{$viewClass['form-group']}">
                <label class="{$viewClass['label']} control-label">{$this->label}</label>
                <div class="{$viewClass['field']}">
                    <div class="input-group">
                        {$this->renderButton()}
                    </div>
                </div>
            </div>
HTML;
    }

    protected function renderButton(): string
    {
        $icon = $this->icon ? "<i class='{$this->icon}'></i>" : '';
        $text = $this->text ?: $this->variables()['value'];
        $html = implode('&nbsp;&nbsp;', array_filter([$icon, $text]));
        $title = $this->title ?: $text;

        return "<button
                    type='button'
                    class='open-layer btn btn-{$this->btnType}'
                    data-url='{$this->url}'
                    data-title='{$title}'
                    data-width='{$this->layerWidth}'
                    data-height='{$this->layerHeight}'
                >
                    {$html}
                </button>";
    }
}
