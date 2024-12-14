<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Tree as Base;

class Tree extends Base
{
    public function __construct($repository = null, ?Closure $callback = null)
    {
        parent::__construct($repository, $callback);

        $this->disableQuickCreateButton();
        $this->disableQuickEditButton();
        $this->showEditButton();
    }
}