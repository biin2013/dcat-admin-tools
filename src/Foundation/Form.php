<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Utils\Helper;
use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Form as Base;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Form extends Base
{
    protected ?Controller $controller;

    /**
     * @throws Exception
     */
    public function __construct(?Controller $controller = null, $repository = null, ?Closure $callback = null, Request $request = null)
    {
        if (is_callable(func_get_arg(1))) {
            $request = $callback;
            $callback = $repository;
            $repository = $controller->model();
        }

        parent::__construct($repository, $callback, $request);
        $this->controller = $controller;

        $this->disableCreatingCheck()
            ->disableEditingCheck()
            ->disableViewCheck()
            ->disabledeleteButton(false);

        $this->submitted(fn(Form $form) => $form->autoValidate());
    }

    public function disableDeleteButton(bool $disable = true): Form
    {
        $disable = $disable || Admin::user()->cannot(Helper::resolvePermissionHttpPath('destroy'));

        return parent::disableDeleteButton($disable);
    }

    /*public function uniqueInParentOnUpdate(string $message = '', string $pidField = 'pid', string $nameField = 'name'): void
    {
        $this->submitted(function (Form $form) use ($message, $pidField, $nameField) {
            if ($form->isEditing()) {
                $has = $form->model()->where($pidField, $form->input($pidField))
                    ->where($form->model()->getKeyName(), '<>', $form->model()->getKey())
                    ->pluck($nameField)
                    ->search($form->input($nameField));

                if ($has !== false) {
                    $message = $message ?: trans('global.validations.already_exists', [
                        'attribute' => trans('global.fields.' . $nameField)
                    ]);
                    $form->responseValidationMessages($nameField, $message);
                }
            }
        });
    }*/

    public function autoValidate(): void
    {
        $this->beforeValidate($this);

        $rules = $this->resolveRules();

        if (empty($rules)) return;

        foreach ($rules as $field => $rule) {
            $this->findFieldByName($field)?->rules($rule, $this->controller->messages($this)[$field] ?? []);
        }
    }

    protected function beforeValidate(Form $form): void
    {
        $this->controller?->beforeValidate($form);
    }

    protected function resolveRules(): array
    {
        $rules = array_merge($this->controller->defaultRules(), $this->controller->rules($this));

        if ($this->isCreating()) {
            $rules = array_merge_recursive($rules, $this->controller->createExtraRules($this));
        } elseif ($this->isEditing()) {
            $rules = array_merge_recursive($rules, $this->controller->updateExtraRules($this));
        }

        return $rules;
    }

    public static function selectApi(
        $form,
        string $field,
        Model $model,
        string $api,
        array $defaultConfig = [],
        string $idField = 'id',
        string $nameField = 'name'
    )
    {
        return $form->select($field)
            ->options(fn($id) => $id ? $model->find($id)->pluck($nameField, $idField) : [])
            ->addDefaultConfig(array_merge(['minimumInputLength' => 0], $defaultConfig))
            ->ajax($api);
    }

    public function selectFromApi(
        string $field,
        Model  $model,
        string $api,
        array  $defaultConfig = [],
        string $idField = 'id',
        string $nameField = 'name'
    )
    {
        return static::selectApi($this, $field, $model, $api, $defaultConfig, $idField, $nameField);
    }
}