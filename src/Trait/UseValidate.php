<?php

namespace Biin2013\DcatAdminTools\Trait;


use Biin2013\DcatAdminTools\Foundation\Form;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

trait UseValidate
{
    public function rules(array $data): array
    {
        return [];
    }

    public function createRules(array $data): array
    {
        return [];
    }

    public function updateRules(array $data): array
    {
        return [];
    }

    public function defaultRules(): array
    {
        return [
            'brief' => ['max:255']
        ];
    }

    public function messages(array $data): array
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

    protected function validateData(
        ?array $data = null,
        ?array $rules = null,
        ?array $messages = null,
        ?array $attributes = null
    ): void
    {
        $data = $data ?? request()->all();

        $validator = Validator::make(
            $data,
            $rules ?? $this->rules($data),
            $messages ?? $this->messages($data),
            $attributes ?? $this->resolveAttributes()
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    protected function validateUnique(string $field, mixed $value, ?string $column = null): Unique
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