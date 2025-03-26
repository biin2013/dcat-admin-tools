<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiController extends Controller
{
    protected string $modelClass;
    protected string $nameField = 'name';
    protected string $idField = 'id';
    protected string $orderField = 'id';
    protected string $order = 'desc';
    protected int $limit = 20;
    protected string $queryField = 'name';
    protected string $queryCondition = 'like';
    protected bool $paginateResponse = true;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(): Collection|LengthAwarePaginator
    {
        $query = request()->get('q');

        $model = $this->order($this->resolveWhere(new $this->modelClass, $query));

        return $this->paginateResponse
            ? $this->paginateResponse($model)
            : $this->simpleResponse($model);
    }

    protected function resolveWhere(Model $model, ?string $query = null)
    {
        if (!$query) return $model;

        if ($this->queryCondition === 'like') {
            $query = '%' . $query . '%';
        }

        return $model->where($this->queryField, $this->queryCondition, $query);
    }

    protected function order($model)
    {
        return $model->orderBy($this->orderField, $this->order);
    }

    protected function paginateResponse($model): LengthAwarePaginator
    {
        return $model->paginate($this->limit, [$this->idField . ' as id', $this->nameField . ' as text']);
    }

    protected function simpleResponse($model): Collection
    {
        return $model->limit($this->limit)->get(['id', $this->nameField . ' as text']);
    }
}
