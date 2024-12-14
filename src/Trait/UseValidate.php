<?php

namespace Biin2013\DcatAdminTools\Trait;


use Dcat\Admin\Form;

trait UseValidate
{
    public function rules(Form $form): array
    {
        return [];
    }

    public function createRules(Form $form): array
    {
        return [];
    }

    public function updateRules(Form $form): array
    {
        return [];
    }

    public function defaultRules(): array
    {
        return [
            'brief' => ['max:255']
        ];
    }

    public function messages(): array
    {
        return [];
    }
}