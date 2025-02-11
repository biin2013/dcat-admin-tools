<?php

namespace Biin2013\DcatAdminTools\Foundation;

class ApiController extends Controller
{
    protected function selectOptions(
        $model,
        string $query = null,
        string $nameField = 'name',
        string $idField = 'id',
        int $limit = 20,
        string $order = 'desc'
    )
    {
        $query = $query ?? request()->get('q');

        if ($query) {
            $model = $model->where($nameField, 'like', '%' . $query . '%');
        }
        return $model
            ->orderBy($idField, $order)
            ->limit($limit)
            ->paginate(null, [$idField . ' as id', $nameField . ' as text']);
    }
}