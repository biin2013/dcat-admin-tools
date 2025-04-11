<?php

namespace Biin2013\DcatAdminTools\Foundation;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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
    protected string $queryField = 'name';
    protected string $queryCondition = 'like';
    protected bool $paginateResponse = true;
    protected string $nameSeparator = ' - ';

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

    protected function resolveNameField(): string|Expression
    {
        if (is_array($this->nameField)) {
            return DB::raw('CONCAT(' . implode(',\'' . $this->nameSeparator . '\',', $this->nameField) . ') as text');
        }

        return $this->nameField . ' as text';
    }

    protected function paginateResponse($model): LengthAwarePaginator
    {
        return $model->paginate($this->limit, [$this->idField . ' as id', $this->resolveNameField()]);
    }

    protected function simpleResponse($model): Collection
    {
        return $model->limit($this->limit)->get(['id', $this->resolveNameField()]);
    }
}
