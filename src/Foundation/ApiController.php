<?php

namespace Biin2013\DcatAdminTools\Foundation;

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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke()
    {
        $query = request()->get('q');

        $model = $this->resolveWhere(new $this->modelClass, $query);

        return $model
            ->orderBy($this->orderField, $this->order)
            ->limit($this->limit)
            ->paginate(null, [$this->idField . ' as id', $this->nameField . ' as text']);
    }

    protected function resolveWhere(Model $model, ?string $query = null)
    {
        if ($this->queryCondition === 'like') {
            $query = '%' . $query . '%';
        }

        return $model->where($this->queryField, $this->queryCondition, $query);
    }
}