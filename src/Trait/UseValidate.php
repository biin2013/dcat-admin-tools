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
    protected string $validateMode = \Dcat\Admin\Form\Builder::MODE_CREATE;

    public function beforeValidate(Form|array $form = null): void
    {

    }

    public function rules(Form|array $form = null): array
    {
        return [];
    }

    public function createExtraRules(Form|array $form = null): array
    {
        return [];
    }

    public function updateExtraRules(Form|array $form = null): array
    {
        return [];
    }

    public function defaultRules(): array
    {
        return [
            'brief' => ['max:255']
        ];
    }

    public function messages(Form|array $form = null): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    public function validateMode(string $mode): static
    {
        $this->validateMode = $mode;

        return $this;
    }

    private function resolveAttributes(array $rules): array
    {
        return array_intersect_key(array_merge(
            Lang::get('global')['fields'],
            Lang::get($this->translation())['fields'] ?? [],
            $this->attributes()
        ), $rules);
    }

    /**
     * @param array|null $rules
     * @param array|null $data
     * @param array|null $messages
     * @param array|null $attributes
     * @param string $attributePrefix
     * @return void
     */
    protected function validateData(
        ?array $rules = null,
        ?array $data = null,
        ?array $messages = null,
        ?array $attributes = null,
        string $attributePrefix = ''
    ): void
    {
        $data = $data ?? request()->all();
        $this->beforeValidate($data);
        $rules = $rules ?? $this->resolveRules($data);
        $messages = $messages ?? $this->messages($data);
        $attributes = $attributes ?? $this->resolveAttributes($rules);
        if ($attributePrefix) {
            array_walk($attributes, fn(&$item) => $item = $attributePrefix . ' ' . $item);
        }

        $validator = Validator::make(
            $data,
            $rules,
            $messages ?? [],
            $attributes
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function resolveRules(Form|array $form = null): array
    {
        $rules = array_merge($this->defaultRules(), $this->rules($form));

        if ($this->validateMode == \Dcat\Admin\Form\Builder::MODE_CREATE) {
            $rules = array_merge_recursive($rules, $this->createExtraRules($form));
        } elseif ($this->validateMode == \Dcat\Admin\Form\Builder::MODE_EDIT) {
            $rules = array_merge_recursive($rules, $this->updateExtraRules($form));
        }

        return $rules;
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
