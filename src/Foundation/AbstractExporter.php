<?php

namespace Biin2013\DcatAdminTools\Foundation;

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
}