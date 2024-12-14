<?php

namespace Biin2013\DcatAdminTools\Trait;

trait UseValidate
{
    public function rules(): array
    {
        return [];
    }

    public function createRules(): array
    {
        return [];
    }

    public function updateRules(): array
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