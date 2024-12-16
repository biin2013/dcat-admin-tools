<?php

namespace Biin2013\DcatAdminTools\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

class BatchRestore extends BatchAction
{
    protected ?string $model;

    // 注意构造方法的参数必须要有默认值
    public function __construct(string $model = null)
    {
        $this->title = trans('global.labels.restore');
        $this->model = $model;
    }

    public function handle(Request $request)
    {
        $model = $request->get('model');

        foreach ((array)$this->getKey() as $key) {
            $model::withTrashed()->findOrFail($key)->restore();
        }

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