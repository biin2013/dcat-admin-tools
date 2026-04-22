<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Biin2013\Spreadsheet\Export;
use Dcat\Admin\Grid\Exporter;
use Dcat\Admin\Grid\Exporters\AbstractExporter as Base;
use Illuminate\Support\Collection;

abstract class AbstractExporter extends Base
{
    public function buildOriginData(?int $page = null, ?int $perPage = null): array|Collection
    {
        $model = $this->getGridModel();

        // current page
        if ($this->scope === Exporter::SCOPE_CURRENT_PAGE) {
            $page = $model->getCurrentPage();
            $perPage = $model->getPerPage();
        }

        $model->usePaginate(false);

        if ($page && $this->scope !== Exporter::SCOPE_SELECTED_ROWS) {
            $perPage = $perPage ?: $this->getChunkSize();

            $model->forPage($page, $perPage);
        }

        $data = $this->grid->processFilter();
        $model->reset();

        return $this->callBuilder($data);
    }

    public function buildData(?int $page = null, ?int $perPage = null)
    {
        return $this->normalize($this->buildOriginData($page, $perPage));
    }

    protected function model($model): void
    {

    }

    public function export()
    {
        $this->model($this->grid->model());

        $export = Export::make($this->resolveSheetData());
        $this->type($export);
        $export->build(public_path('export'))->export()->download();
    }

    protected function type(Export $export): void
    {
        $export->xlsx();
    }

    protected function resolveSheetData(): array
    {
        return array_filter(array_merge([$this->defaultSheetData()], $this->extraSheetData()));
    }

    protected function config(): array
    {
        return [];
    }

    protected function header(): array
    {
        return [];
    }

    protected function data(): array
    {
        return $this->buildOriginData()->toArray();
    }

    protected function defaultSheetData(): array
    {
        return [
            'config' => $this->config(),
            'header' => $this->header(),
            'data' => $this->data()
        ];
    }

    protected function extraSheetData(): array
    {
        return [[]];
    }
}
