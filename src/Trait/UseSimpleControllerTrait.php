<?php

namespace Biin2013\DcatAdminTools\Trait;

use Biin2013\DcatAdminTools\Foundation\Form;
use Biin2013\DcatAdminTools\Foundation\Grid;
use Biin2013\DcatAdminTools\Foundation\Show;
use Dcat\Admin\Grid\Filter;

trait UseSimpleControllerTrait
{
    protected array $columns = ['name', 'brief', 'created_at'];
    protected array $formColumns = ['name' => 'text', 'brief' => 'textarea'];
    protected array $formRules = ['name' => ['required', 'max:45']];

    protected bool $nameQuickSearch = true;
    protected bool $useTrashFilter = true;

    protected function grid()
    {
        return Grid::make($this, function (Grid $grid) {
            array_map(fn($column) => $grid->column($column), $this->columns);


            if ($this->nameQuickSearch) {
                $grid->customQuickSearch();
            }

            if ($this->useTrashFilter) {
                $grid->filter(fn(Filter $filter) => $this->customFilter($filter));
                $grid->useTrashFilter();
            }
        });
    }

    protected function customFilter(Filter $filter): void
    {
        $filter->like($this->columns[0]);
    }

    protected function form()
    {
        return Form::make($this, function (Form $form) {
            foreach ($this->formColumns as $column => $type) {
                $form->$type($column);
            }
        });
    }

    public function rules(Form $form): array
    {
        return $this->formRules;
    }

    protected function detail($id): Show
    {
        return Show::make($id, $this->model(), function (Show $show) {
            array_map(fn($column) => $show->field($column), $this->columns);
        });
    }
}