<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Actions\BatchRestore;
use Biin2013\DcatAdminTools\Actions\Restore;
use Biin2013\DcatAdminTools\Foundation\Grid\Column;
use Biin2013\DcatAdminTools\Utils\Helper;
use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid as Base;
use Dcat\Admin\Grid\Displayers\Actions;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Grid\Tools\BatchActions;
use Exception;
use Illuminate\Support\Facades\Schema;

class Grid extends Base
{
    protected ?Controller $controller;
    protected string $deleteMessageField = 'name';
    protected string $deleteMessage = '';
    protected ?Closure $deleteMessageCallback = null;
    protected string $deleteMessageTitle = '';
    protected ?Closure $deleteMessageTitleCallback = null;
    protected string $deleteMessageSeparator = '：';

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

        if (request('_scope_') == 'trashed') {
            $this->model()->orderBy('deleted_at', 'desc');
        } else if (
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
        $this->paginate(10);
        $this->toolsWithOutline(false);
        $this->filter()->expand(false);
        $this->disableQuickEditButton()
            ->showCreateButton()
            ->showEditButton()
            ->showDeleteButton()
            ->showBatchDelete();
    }

    public function useTrashFilter(Closure $closure = null): static
    {
        return $this->trashFilter()->restoreAction()->batchRestoreAction()->filter(fn(Filter $filter) => $closure && $closure($filter));
    }

    public function trashFilter(): static
    {
        if (Admin::user()->cannot(Helper::resolvePermissionHttpPath('trash'))) return $this;

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
        if (Admin::user()->cannot(Helper::resolvePermissionHttpPath('restore'))) return $this;

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
        if (Admin::user()->cannot(Helper::resolvePermissionHttpPath('restore'))) return $this;

        $model = $modelClass ?? $this->controller->modelClass();
        $this->batchActions(function (BatchActions $batch) use ($model) {
            if (request('_scope_') == 'trashed') {
                $batch->add(new BatchRestore($model));
            }
        });

        return $this;
    }

    public function showQuickEditButton(bool $val = true): Grid
    {
        return $this->disableQuickEditButton(
            Admin::user()->cannot(Helper::resolvePermissionHttpPath('update'))
        );
    }

    public function showEditButton(bool $val = true): Grid
    {
        return $this->disableEditButton(
            Admin::user()->cannot(Helper::resolvePermissionHttpPath('update'))
        );
    }

    public function showCreateButton(bool $val = true): Grid
    {
        return $this->disableCreateButton(
            Admin::user()->cannot(Helper::resolvePermissionHttpPath('store'))
        );
    }

    public function showDeleteButton(bool $val = true): Grid
    {
        return $this->disableDeleteButton(
            Admin::user()->cannot(Helper::resolvePermissionHttpPath('destroy'))
        );
    }

    public function showBatchDelete(bool $val = true): Grid
    {
        return $this->disableBatchDelete(
            Admin::user()->cannot(Helper::resolvePermissionHttpPath('destroy'))
        );
    }

    public function customQuickSearch($field = 'name', $label = null): Base\Tools\QuickSearch
    {
        return $this->quickSearch($field)->placeholder($label ?? trans('admin.name'));
    }

    public function newColumn($field = '', $label = ''): Column
    {
        $column = new Column($field, $label);
        $column->setGrid($this);

        return $column;
    }

    public function setDeleteMessageField(string $field): static
    {
        $this->deleteMessageField = $field;

        return $this;
    }

    public function setDeleteMessage(Closure $closure): static
    {
        $this->deleteMessageCallback = $closure;

        return $this;
    }

    public function getDeleteMessage($row): string
    {
        return $this->deleteMessageCallback
            ? call_user_func($this->deleteMessageCallback, $row)
            : ($row->{$this->deleteMessageField} ?? '');
    }

    public function setDeleteMessageTitle(Closure $closure): static
    {
        $this->deleteMessageTitleCallback = $closure;

        return $this;
    }

    public function getDeleteMessageTitle($row): string
    {
        return $this->deleteMessageTitleCallback
            ? call_user_func($this->deleteMessageTitleCallback, $row)
            : $this->deleteMessageTitle;
    }

    public function setDeleteMessageSeparator(string $separator): static
    {
        $this->deleteMessageSeparator = $separator;

        return $this;
    }

    public function getDeleteMessageSeparator(): string
    {
        return $this->deleteMessageSeparator;
    }
}