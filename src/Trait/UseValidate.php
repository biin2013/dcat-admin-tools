<?php

namespace Biin2013\DcatAdminTools\Trait;


use Biin2013\DcatAdminTools\Foundation\Form;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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

    protected function validateUnique(Form $form, string $field = 'name'): Unique
    {
        return Rule::unique($this->modelClass)
            ->where(fn(Builder $query) => $query->where($field, $form->input($field)));
    }

    protected function validateUniqueIgnore(
        Form    $form,
        string  $field = 'name',
        mixed   $ignore = null,
        ?string $column = null
    ): Unique
    {
        return $this->validateUnique($form, $field)
            ->ignore($ignore ?? $form->getKey(), $column);
    }
}