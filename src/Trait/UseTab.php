<?php

namespace Biin2013\DcatAdminTools\Trait;

use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait UseTab
{
    protected array $lang;

    public function index(Content $content): Content
    {
        return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->getTabs() . $this->grid());
    }

    protected function getTabs(): string|Tab
    {
        $tab = new Tab();
        $tabs = $this->resolveTabs();

        if (count($tabs) == 1) return '';

        $this->lang = Lang::get('menu.titles');

        foreach ($tabs as $v) {
            $tab->addLink(
                $this->resolveTitle($v),
                admin_route(str_replace('/', '.', ltrim($v, '/'))),
                $this->resolveActiveTab($v)
            );
        }

        return $tab->withCard();
    }

    protected function resolveTitle(string $path): string
    {
        return $this->resolveTitleByRoute($path);
    }

    protected function resolveTitleByRoute(string $path): string
    {
        if ($title = $this->lang[$path] ?? null) return $title;

        $data = explode('/', $path);
        $label = $data[count($data) - 2];

        return trans('global.labels.' . Str::singular($label));
    }

    protected function resolveTabs(): array
    {
        return array_filter(
            $this->tabs(),
            fn($tab) => Admin::user()->can($tab)
        );
    }

    protected function resolveActiveTab(string $path): bool
    {
        $uri = request()->route()->uri();
        $data = explode('/', $path);
        array_shift($data);
        array_pop($data);

        return $uri == config('admin.route.prefix') . '/' . implode('/', $data);
    }

    abstract protected function tabs(): array;
}