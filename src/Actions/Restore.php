<?php

namespace Biin2013\DcatAdminTools\Actions;

use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

class Restore extends RowAction
{
    protected ?string $model;

    // 注意构造方法的参数必须要有默认值
    public function __construct(string $model = null)
    {
        $this->title = '<span class="feather icon-refresh-cw"></span> ' . trans('global.labels.restore');
        $this->model = $model;
    }

    public function handle(Request $request)
    {
        $key = $this->getKey();
        $model = $request->get('model');

        $model::withTrashed()->findOrFail($key)->restore();

        return $this->response()->success(
            trans('global.labels.already') . trans('global.labels.restore')
        )->refresh();
    }

    public function confirm()
    {
        return [trans('global.labels.confirm') . trans('global.labels.restore') . ' ?'];
    }

    public function parameters()
    {
        return [
            'model' => $this->model,
        ];
    }
}