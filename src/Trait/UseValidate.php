<?php

namespace Biin2013\DcatAdminTools\Trait;


use Biin2013\DcatAdminTools\Foundation\Form;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;

trait UseValidate
{
    public function beforeValidate(Form $form = null)
    {

    }

    public function rules(Form $form): array
    {
        return [];
    }

    public function createExtraRules(Form $form): array
    {
        return [];
    }

    public function updateExtraRules(Form $form): array
    {
        return [];
    }

    public function defaultRules(): array
    {
        return [
            'brief' => ['max:255']
        ];
    }

    public function messages(Form $form = null): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    private function resolveAttributes(): array
    {
        return array_merge(
            Lang::get('global')['fields'],
            Lang::get($this->translation())['fields'] ?? [],
            $this->attributes()
        );
    }

    /**
     * @param array $rules
     * @param array|null $data
     * @param array|null $messages
     * @param array|null $attributes
     * @return void
     * @throws ValidationException
     */
    protected function validateData(
        array  $rules,
        ?array $data = null,
        ?array $messages = null,
        ?array $attributes = null
    ): void
    {
        $data = $data ?? request()->all();

        $validator = Validator::make(
            $data,
            $rules,
            $messages ?? [],
            $attributes ?? $this->resolveAttributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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