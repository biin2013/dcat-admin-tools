<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Closure;
use Dcat\Admin\Form as Base;
use Illuminate\Http\Request;

class Form extends Base
{
    public function __construct($repository = null, ?Closure $callback = null, Request $request = null)
    {
        parent::__construct($repository, $callback, $request);

        $this->disableCreatingCheck()
            ->disableEditingCheck()
            ->disableViewCheck();
    }

    /**
     * @param string $message
     * @param string $pidField
     * @param string $nameField
     * @return void
     */
    public function uniqueInParentOnUpdate(string $message = '', string $pidField = 'pid', string $nameField = 'name'): void
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
    }
}