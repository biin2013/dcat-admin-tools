<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Grid\Filter;

class SimpleController extends Controller
{
    protected array $columns = ['name', 'brief', 'created_at'];
    protected array $detailColumns = [];
    protected array $formColumns = ['name' => 'text', 'brief' => 'textarea'];
    protected array $formRules = ['name' => ['required', 'max:45']];
    protected bool $nameQuickSearch = true;
    protected bool $useTrashFilter = true;

    protected bool $create = true;
    protected bool $edit = true;
    protected bool $delete = true;
    protected bool $show = true;

    protected function grid(): Grid
    {
        return $this->customGrid(Grid::make($this, function (Grid $grid) {
            $custom = $this->customGridColumn();
            foreach ($this->columns as $column) {
                isset($custom[$column])
                    ? $custom[$column]($grid)
                    : $this->defaultGridColumn($grid, $column);
            }

            !$this->create && $grid->disableCreateButton();
            !$this->show && $grid->disableViewButton();
            !$this->edit && $grid->disableEditButton()->disableQuickEditButton();
            !$this->delete && $grid->disableDeleteButton();

            if ($this->nameQuickSearch) {
                $grid->customQuickSearch();
            }

            if ($this->useTrashFilter) {
                $grid->filter(fn(Filter $filter) => $this->customFilter($filter));
                $grid->useTrashFilter();
            }
        }));
    }

    protected function labels(): array
    {
        return [];
    }

    protected function customGrid(Grid $grid): Grid
    {
        return $grid;
    }

    protected function customGridColumn(): array
    {
        return [];
    }

    protected function defaultGridColumn(Grid $grid, string $column): void
    {
        $grid->column($column, $this->labels()[$column] ?? '');
    }

    protected function customFilter(Filter $filter): void
    {
        $filter->like($this->columns[0]);
    }

    protected function form(): Form
    {
        return $this->customForm(Form::make($this, function (Form $form) {
            $custom = $this->customFormField();
            foreach ($this->formColumns as $column => $type) {
                isset($custom[$column])
                    ? $custom[$column]($form)
                    : $this->defaultFormField($form, $type, $column);
            }
        }));
    }

    protected function customForm(Form $form): Form
    {
        return $form;
    }

    protected function customFormField(): array
    {
        return [];
    }

    protected function defaultFormField(Form $form, string $type, string $column): void
    {
        $form->$type($column, $this->labels()[$column] ?? null);
    }

    public function rules(Form $form): array
    {
        return $this->formRules;
    }

    protected function detail($id): Show
    {
        return $this->customDetail(Show::make($id, $this->model(), function (Show $show) {
            $custom = $this->customDetailField($show);
            foreach ($this->detailColumns ?: $this->columns as $column) {
                isset($custom[$column])
                    ? $custom[$column]($show)
                    : $this->defaultDetailField($show, $column);
            }

            !$this->edit && $show->disableEditButton()->disableQuickEdit();
            !$this->delete && $show->disableDeleteButton();
        }));
    }

    protected function customDetail(Show $show): Show
    {
        return $show;
    }

    protected function customDetailField(): array
    {
        return [];
    }

    protected function defaultDetailField(Show $show, string $column): void
    {
        $show->field($column, $this->labels()[$column] ?? '');
    }
}