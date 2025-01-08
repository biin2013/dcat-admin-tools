<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Dcat\Admin\Grid\Filter;

class SimpleController extends Controller
{
    protected array $columns = ['name', 'brief', 'created_at'];
    protected array $formColumns = ['name' => 'text', 'brief' => 'textarea'];
    protected array $formRules = ['name' => ['required', 'max:45']];

    protected bool $nameQuickSearch = true;
    protected bool $useTrashFilter = true;

    protected function grid(): Grid
    {
        return $this->customGrid(Grid::make($this, function (Grid $grid) {
            array_map(fn($column) => $grid->column($column), $this->columns);

            if ($this->nameQuickSearch) {
                $grid->customQuickSearch();
            }

            if ($this->useTrashFilter) {
                $grid->filter(fn(Filter $filter) => $this->customFilter($filter));
                $grid->useTrashFilter();
            }
        }));
    }

    protected function customGrid(Grid $grid): Grid
    {
        return $grid;
    }

    protected function customFilter(Filter $filter): void
    {
        $filter->like($this->columns[0]);
    }

    protected function form(): Form
    {
        return $this->customForm(Form::make($this, function (Form $form) {
            foreach ($this->formColumns as $column => $type) {
                $form->$type($column);
            }
        }));
    }

    protected function customForm(Form $form): Form
    {
        return $form;
    }

    public function rules(Form $form): array
    {
        return $this->formRules;
    }

    protected function detail($id): Show
    {
        return $this->customDetail(Show::make($id, $this->model(), function (Show $show) {
            array_map(fn($column) => $show->field($column), $this->columns);
        }));
    }

    protected function customDetail(Show $show): Show
    {
        return $show;
    }
}