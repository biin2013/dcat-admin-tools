<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Closure;
use Dcat\Admin\Form as Base;
use Exception;
use Illuminate\Http\Request;

class Form extends Base
{
    protected Controller $controller;

    /**
     * @throws Exception
     */
    public function __construct(?Controller $controller = null, $repository = null, ?Closure $callback = null, Request $request = null)
    {
        if (func_num_args() === 2) {
            $callback = $repository;
            $repository = $controller->model();
        }

        parent::__construct($repository, $callback, $request);
        $this->controller = $controller;

        $this->disableCreatingCheck()
            ->disableEditingCheck()
            ->disableViewCheck();

        $this->submitted(fn(Form $form) => $form->autoValidate());
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
        $rules = $this->resolveRules();

        if (empty($rules)) return;

        foreach ($rules as $field => $rule) {
            $this->findFieldByName($field)->rules($rule);
        }
    }

    protected function resolveRules(): array
    {
        $rules = $this->controller->rules();

        if ($this->isCreating()) {
            $rules = array_merge_recursive($rules, $this->controller->createRules());
        } elseif ($this->isEditing()) {
            $rules = array_merge_recursive($rules, $this->controller->updateRules());
        }

        return $rules;
    }
}