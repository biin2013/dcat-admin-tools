<?php

namespace Biin2013\DcatAdminTools;

use Closure;
use Dcat\Admin\Grid as Base;

class Grid extends Base
{
    private static int $page = 10;
    private static bool $toolsWithOutline = false;
    private static bool $disableQuickEditButton = true;

    /**
     * @param $repository
     * @param Closure|null $builder
     * @param $request
     */
    public function __construct($repository = null, ?Closure $builder = null, $request = null)
    {
        parent::__construct($repository, $builder, $request);

        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $this->paginate(self::$page);
        $this->toolsWithOutline(self::$toolsWithOutline);
        $this->disableQuickEditButton(self::$disableQuickEditButton);
    }

    /**
     * @param int $page
     * @return void
     */
    public static function setPage(int $page = 20): void
    {
        self::$page = $page;
    }

    /**
     * @param bool $value
     * @return void
     */
    public static function setToolsWithOutline(bool $value = true): void
    {
        self::$toolsWithOutline = $value;
    }

    public static function setShowEditButton(bool $value = true): void
    {
        self::$disableQuickEditButton = !$value;
    }
}