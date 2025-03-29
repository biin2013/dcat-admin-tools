<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Trait\UseValidate;
use Dcat\Admin\Form;
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
        if (!$this->translation) {
            $this->translation = $this->resolveTranslation($this);
        }

        return $this->translation;
    }

    protected function resolveTranslation(Controller|string $class): string
    {
        if ($class instanceof Controller) {
            $class = get_class($class);
        }

        $path = array_slice(explode('\\', $class), 3);
        $controller = substr(array_pop($path), 0, -10);
        $path[] = $controller;

        return strtolower(implode('/', $path));
    }

    public function filterHasManyRemoveItem(array &$data, string $field, bool $remove = true): void
    {
        $data[$field] = $data[$field] ?? [];
        $data[$field] = $this->filterRemoveItem($data[$field], $remove);
    }

    protected function filterRemoveItem(array $data, bool $remove = true): array
    {
        $filter = array_filter($data, fn($item) => $item[Form::REMOVE_FLAG_NAME] != 1);

        if ($remove) {
            $filter = array_map(function ($item) {
                unset($item[Form::REMOVE_FLAG_NAME]);
                return $item;
            }, $filter);
        }

        return array_values($filter);
    }

    protected function trans(string $key): Application|array|string|Translator
    {
        return trans($this->translation() . '.' . $key);
    }

    protected function transValidations(string $key): Application|array|string|Translator
    {
        return $this->trans('validations.' . $key);
    }

    protected function transFields(string $key): Application|array|string|Translator
    {
        return $this->trans('fields.' . $key);
    }

    protected function transLabels(string $key): Application|array|string|Translator
    {
        return $this->trans('labels.' . $key);
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