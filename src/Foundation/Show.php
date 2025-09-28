<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Foundation\Show\Field;
use Biin2013\DcatAdminTools\Utils\Helper;
use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Show as Base;

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

    public function disableHeader(): Show
    {
        $this->disableDeleteButton();
        $this->disableEditButton();
        $this->disableListButton();
        $this->panel->title('');

        return $this;
    }

    protected function addField($name, $label = ''): Field
    {
        $field = new Field($name, $label);

        $field->setParent($this);

        $this->overwriteExistingField($name);

        $this->fields->push($field);

        return $field;
    }
}