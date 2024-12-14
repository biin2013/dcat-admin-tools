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

    protected function validateUnique(Form $form, string $field, ?string $column = null): Unique
    {
        return Rule::unique($this->modelClass)
            ->where(fn(Builder $query) => $query->where($column ?? $field, $form->input($field)));
    }

    protected function validateUniqueIgnore(
        Form    $form,
        string  $field,
        ?string $column = null,
        mixed   $ignore = null,
        ?string $idColumn = null
    ): Unique
    {
        return $this->validateUnique($form, $field, $column)
            ->ignore($ignore ?? $form->getKey(), $idColumn);
    }
}