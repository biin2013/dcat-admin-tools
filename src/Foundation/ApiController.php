<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiController extends Controller
{
    protected string $modelClass;
    protected string|array $nameField = 'name';
    protected string $idField = 'id';
    protected string $orderField = 'id';
    protected string $order = 'desc';
    protected int $limit = 20;
    protected string|array $queryField = 'name';
    protected string $queryCondition = 'like';
    protected bool $paginateResponse = true;
    protected string $nameSeparator = ' - ';
    protected array $selectFields = [];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke()
    {
        $query = request()->get('q');
        $this->paginateResponse = request()->get('p', $this->paginateResponse);

        $model = $this->order($this->resolveWhere(new $this->modelClass, $query))
            ->withoutGlobalScope('order');

        return $this->paginateResponse
            ? $this->paginateResponse($model)
            : $this->simpleResponse($model);
    }

    protected function initModel($model)
    {
        return $model;
    }

    protected function resolveWhere($model, ?string $query = null)
    {
        $model = $this->initModel($model);

        if (!$query) return $model;

        if ($this->queryCondition === 'like') {
            $query = '%' . $query . '%';
        }

        return is_array($this->queryField)
            ? $model->where(fn($q) => array_map(fn($field) => $q->orWhere($field, $this->queryCondition, $query), $this->queryField))
            : $model->where($this->queryField, $this->queryCondition, $query);
    }

    protected function order($model)
    {
        return $model->orderBy($this->orderField, $this->order);
    }

    protected function resolveNameField(): string|Expression
    {
        if (is_array($this->nameField)) {
            return DB::raw('CONCAT(' . implode(',\'' . $this->nameSeparator . '\',', $this->nameField) . ') as text');
        }

        return $this->nameField . ' as text';
    }

    protected function simpleResponse($model)
    {
        return $this->customSimpleResponse($model->get($this->selectFields ?: ['id', $this->resolveNameField()]));
    }

    protected function paginateResponse($model)
    {
        $fields = $this->selectFields ?: [$this->idField . ' as id', $this->resolveNameField()];
        return $this->customPaginateResponse($model->paginate($this->limit, $fields));
    }

    protected function customSimpleResponse($response)
    {
        return $response;
    }

    protected function customPaginateResponse($response)
    {
        return $response->setCollection($this->customSimpleResponse($response));
    }
}
