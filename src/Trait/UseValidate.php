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
    public function rules(?Form $form = null): array
    {
        return [];
    }

    public function createRules(?Form $form = null): array
    {
        return [];
    }

    public function updateRules(?Form $form = null): array
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

    protected function attributes(): array
    {
        return [];
    }

    protected function resolveAttributes(): array
    {
        return array_merge(
            Lang::get('global')['fields'],
            Lang::get($this->translation())['fields'] ?? [],
            $this->attributes()
        );
    }

    protected function afterValidate(array $data): void
    {

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
            $rules ?? $this->rules(),
            $messages ?? $this->messages(),
            $attributes ?? $this->resolveAttributes()
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $this->afterValidate($data);
    }

    protected function validateUnique(string $field, mixed $value, ?string $column = null): Unique
    {
        return Rule::unique($this->modelClass)
            ->where(fn(Builder $query) => $query->where($column ?? $field, $value));
    }

    protected function validateUniqueIgnore(
        string  $field,
        mixed   $value,
        mixed   $ignore,
        ?string $column = null,
        ?string $idColumn = null
    ): Unique
    {
        return $this->validateUnique($field, $value, $column)
            ->ignore($ignore, $idColumn);
    }
}