<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Utils\Helper;
use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Show as Base;
use Dcat\Admin\Widgets\Modal;

class Show extends Base
{
    public function __construct($id = null, $model = null, ?Closure $builder = null)
    {
        parent::__construct($id, $model, $builder);

        $this->disableQuickEdit()
            ->disableEditButton(false)
            ->disableDeleteButton(false);
    }

    public function showQuickEdit(?string $width = null, ?string $height = null): Show
    {
        if (Admin::user()->cannot(Helper::resolvePermissionHttpPath('update'))) return $this;

        return parent::showQuickEdit($width, $height);
    }

    public function disableEditButton(bool $disable = true): Show
    {
        $disable = $disable || Admin::user()->cannot(Helper::resolvePermissionHttpPath('update'));

        return parent::disableEditButton($disable);
    }

    public function disableDeleteButton(bool $disable = true): Show
    {
        $disable = $disable || Admin::user()->cannot(Helper::resolvePermissionHttpPath('destroy'));

        return parent::disableDeleteButton($disable);
    }

    public function modal(
        string $field,
        mixed  $content,
        string $label = '',
        string $title = '',
        string $btnType = 'primary',
        string $size = 'lg'
    ): void
    {
        $this->field($field)->as(function ($value) use ($content, $label, $title, $btnType, $size) {
            $label = $label ?: $value;
            if (empty($label)) {
                return '';
            }

            return Modal::make()
                ->title($title ?: $label)
                ->size($size)
                ->scrollable()
                ->body($content)
                ->button('<button class="btn btn-sm btn-' . $btnType . '" type="button">' . $label . '</button>');
        })->unescape();
    }
}