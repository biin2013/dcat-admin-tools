<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Trait\UseValidate;
use Dcat\Admin\Http\Controllers\AdminController;
use Exception;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class Controller extends AdminController
{
    use UseValidate;

    protected string $modelClass;

    protected function title(): string
    {
        $titles = trans('menu.titles');
        $global = trans('global.labels');
        $uri = explode('.', request()->route()->getName());
        array_pop($uri);
        $uri = array_slice($uri, 2);

        $path = '';
        $title = [];
        foreach ($uri as $value) {
            $path .= '/' . $value;
            if (isset($titles[$path])) {
                $title[] = $titles[$path];
            } else {
                $key = Str::singular($value);
                if (isset($global[$key])) {
                    $title[] = $global[$key];
                }
            }
        }

        return implode(' <span class="text-secondary">/</span> ', $title);
    }

    /**
     * @throws Exception
     */
    public function model()
    {
        if (!$this->modelClass) {
            throw new Exception('modelClass is required');
        }

        return new $this->modelClass;
    }

    public function modelClass(): string
    {
        return $this->modelClass;
    }

    protected function getCurrentUri(): string
    {
        return substr(request()->route()->uri(), strlen(config('admin.route.prefix')));
    }

    protected function translation(): string
    {
        if ($this->translation) {
            return $this->translation;
        }

        $path = array_slice(explode('\\', get_class($this)), 3);
        $controller = substr(array_pop($path), 0, -10);
        $path[] = $controller;

        return strtolower(implode('/', $path));
    }

    protected function yesNoOptions(): array
    {
        return trans('global.options.yes_no');
    }

    protected function filterHasManyRemoveItem(array &$data, string $field): void
    {
        $data[$field] = $data[$field] ?? [];
        $data[$field] = array_values(array_filter($data[$field], fn($item) => $item['_remove_'] != 1));
    }

    protected function trans(string $key): Application|array|string|Translator
    {
        return trans($this->translation() . '.' . $key);
    }

    protected function transValidations(string $key): Application|array|string|Translator
    {
        return $this->trans('validations.' . $key);
    }

    protected function getIndexRoute(): string
    {
        $routeName = request()->route()->getName();
        $route = explode('.', $routeName);
        array_pop($route);
        $route[] = 'index';

        return implode('.', $route);
    }
}