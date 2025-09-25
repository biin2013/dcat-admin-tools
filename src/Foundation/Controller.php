<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\DcatAdminTools\Trait\UseValidate;
use Biin2013\DcatAdminTools\Utils\Helper;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Exception;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class Controller extends AdminController
{
    use UseValidate;

    protected string $modelClass;

    protected string $uniqueField = 'name';
    protected string $uniqueColumn;

    public function createExtraRules(Form $form): array
    {
        return [
            $this->uniqueField => [$this->validateUnique($form, $this->uniqueColumn ?? $this->uniqueField)]
        ];
    }

    public function updateExtraRules(Form $form): array
    {
        return [
            $this->uniqueField => [$this->validateUniqueIgnore($form, $this->uniqueColumn ?? $this->uniqueField)]
        ];
    }

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

    public function filterHasManyRemoveItem(array &$data, string $field, bool $resort = true, bool|array $remove = true): void
    {
        $data[$field] = $data[$field] ?? [];
        $data[$field] = Helper::filterRemoveItem($data[$field], $remove, $resort);
    }

    public function trans(string $key, array $replace = []): Application|array|string|Translator
    {
        return trans($this->translation() . '.' . $key, $replace);
    }

    public function transValidations(string $key, array $replace = []): Application|array|string|Translator
    {
        return $this->trans('validations.' . $key, $replace);
    }

    public function transFields(string $key, array $replace = []): Application|array|string|Translator
    {
        return $this->trans('fields.' . $key, $replace);
    }

    public function transLabels(string $key, array $replace = []): Application|array|string|Translator
    {
        return $this->trans('labels.' . $key, $replace);
    }

    protected function getIndexRoute(): string
    {
        $routeName = request()->route()->getName();
        $route = explode('.', $routeName);
        array_pop($route);
        $route[] = 'index';

        return implode('.', $route);
    }

    protected function jsonResponse(string $message = null, string|bool $redirect = null): JsonResponse
    {
        $response = Admin::json()->success($message ?? trans('admin.save_succeeded'));
        if ($redirect === false) return $response;

        return $response->redirect($redirect ?? route($this->getIndexRoute()));
    }
}