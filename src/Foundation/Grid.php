<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Actions\BatchRestore;
use Biin2013\DcatAdminTools\Actions\Restore;
use Closure;
use Dcat\Admin\Grid as Base;
use Dcat\Admin\Grid\Displayers\Actions;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Grid\Tools\BatchActions;
use Exception;
use Illuminate\Support\Facades\Schema;

class Grid extends Base
{
    private static int $page = 10;
    private static bool $toolsWithOutline = false;
    private static bool $disableQuickEditButton = true;
    protected ?Controller $controller;


    /**
     * @param Controller|null $controller
     * @param $repository
     * @param Closure|null $builder
     * @param $request
     * @throws Exception
     */
    public function __construct(
        ?Controller $controller = null,
                    $repository = null,
        ?Closure    $builder = null,
                    $request = null
    )
    {
        if (is_callable(func_get_arg(1))) {
            $request = $builder;
            $builder = $repository;
            $repository = $controller->model();
        }

        parent::__construct($repository, $builder, $request);

        if (
            $this->model()->repository() &&
            Schema::hasColumn($this->model()->repository()->model()->getTable(), 'id')
        ) {
            $this->model()->orderBy('id', 'desc');
        }

        $this->controller = $controller;

        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $this->column('id');
        $this->paginate(self::$page);
        $this->toolsWithOutline(self::$toolsWithOutline);
        $this->disableQuickEditButton(self::$disableQuickEditButton);
        $this->filter()->expand(false);
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

    public function useTrashFilter(): static
    {
        $this->trashFilter()->restoreAction()->batchRestoreAction();

        return $this;
    }

    public function trashFilter(): static
    {
        $this->filter(function (Filter $filter) {
            $filter->scope('trashed', trans('global.labels.trash'))->onlyTrashed();
        });

        if (request('_scope_') == 'trashed') {
            $this->column('deleted_at');
        }

        return $this;
    }

    public function restoreAction(?string $modelClass = null): static
    {
        $model = $modelClass ?? $this->controller->modelClass();
        $this->actions(function (Actions $actions) use ($model) {
            if (request('_scope_') == 'trashed') {
                $actions->append(new Restore($model));
            }
        });

        return $this;
    }

    public function batchRestoreAction(?string $modelClass = null): static
    {
        $model = $modelClass ?? $this->controller->modelClass();
        $this->batchActions(function (BatchActions $batch) use ($model) {
            if (request('_scope_') == 'trashed') {
                $batch->add(new BatchRestore($model));
            }
        });

        return $this;
    }
}