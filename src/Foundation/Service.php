<?php

namespace Biin2013\DcatAdminTools\Foundation;

class Service
{
    protected string $baseLangPath = '';
    protected array $data = [];

    public static function make(array $data = []): static
    {
        return new static($data);
    }

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    protected function trans(string $key, array $replace = [])
    {
        return trans(ltrim(rtrim($this->baseLangPath, '.') . '.', '.') . $key, $replace);
    }

    protected function transFields(string $key, array $replace = [])
    {
        return $this->trans('fields.' . $key, $replace);
    }

    protected function transValidations(string $key, array $replace = [])
    {
        return $this->trans('validations.' . $key, $replace);
    }
}