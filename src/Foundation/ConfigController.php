<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Utils\Helper;
use Dcat\Admin\Admin;
use Dcat\Admin\Form\Builder;

class ConfigController extends Controller
{
    protected string $group;

    protected function grid()
    {
        return $this->form()->render();
    }

    protected function form()
    {
        return Form::make(null, null, function (Form $form) {
            $form->disableHeader();
            $form->disableResetButton();
            $this->resolveForm($form);
            $form->action(request()->url() . '/1');
            $form->builder()->mode(Builder::MODE_EDIT);

            if (Admin::user()->cannot(Helper::resolvePermissionHttpPath('update'))) {
                $form->disableSubmitButton();
            }
        });
    }

    private function resolveForm(Form $form): void
    {
        $config = AdminConfig::get($this->group);
        $customForm = $this->customFormItem();
        foreach ($config as $item) {
            isset($customForm[$item->key])
                ? $customForm[$item->key]($form, $item->value)
                : $this->resolveFormItem($form, $item->key, $item->type, $item->value);

        }
    }

    private function resolveFormItem(Form $form, string $key, string $type, mixed $value): void
    {
        match ($type) {
            'bool' => $form->switch($key)->value($value),
            'int' => $form->number($key)->value($value),
            'float' => $form->decimal($key)->value($value),
            default => $form->text($key)->value($value)
        };
    }

    protected function customFormItem(): array
    {
        return [];
    }

    public function update($id)
    {
        $keys = AdminConfig::getKeys($this->group);
        $data = array_intersect_key(request()->all(), array_flip($keys));
        AdminConfig::setMany($this->group, $data);

        return Admin::json()->success(trans('admin.save_succeeded'));
    }
}
