<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Utils\Helper;
use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Tree as Base;

class Tree extends Base
{
    public function __construct($repository = null, ?Closure $callback = null)
    {
        parent::__construct($repository, $callback);

        $this->disableQuickCreateButton();
        $this->disableQuickEditButton();
        $this->disableCreateButton(false);
        $this->disableEditButton(false);
        $this->disableSaveButton(false);
        $this->disableDeleteButton(false);
    }

    private function resolveButtonValueWithPermission(bool $value, string $method): bool
    {
        return $value || Admin::user()->cannot(Helper::resolvePermissionHttpPath($method));
    }

    public function disableQuickCreateButton(bool $value = true): void
    {
        parent::disableQuickCreateButton($this->resolveButtonValueWithPermission($value, 'store'));
    }


    public function disableQuickEditButton(bool $value = true): void
    {
        parent::disableQuickEditButton($this->resolveButtonValueWithPermission($value, 'update'));
    }

    public function disableSaveButton(bool $value = true): void
    {
        parent::disableSaveButton($this->resolveButtonValueWithPermission($value, 'update'));
    }

    public function disableCreateButton(bool $value = true): void
    {
        parent::disableCreateButton($this->resolveButtonValueWithPermission($value, 'store'));
    }

    public function disableEditButton(bool $value = true): void
    {
        parent::disableEditButton($this->resolveButtonValueWithPermission($value, 'update'));
    }

    public function disableDeleteButton(bool $value = true): void
    {
        parent::disableDeleteButton($this->resolveButtonValueWithPermission($value, 'destroy'));
    }
}