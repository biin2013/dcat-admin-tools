<?php

namespace Biin2013\DcatAdminTools\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;


class ImportDb extends Command
{
    protected $signature = 'admin:import-db
                            { file : import file path , default start with database_path/imports, when start with / , it will be with base_path }
                            { --I|ignore-fields=* : ignore fields when update data has exists by unique_fields }
                            { --D|delete : delete db data when not update or insert}
                            ';

    protected $description = 'import data to database';

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->info('import database start');
        $file = $this->argument('file');
        $path = starts_with($file, '/')
            ? base_path($file)
            : database_path('imports/' . $file);

        DB::beginTransaction();
        try {
            $data = require $path . '.php';
            $config = $this->resolveConfig($data['config'] ?? []);
            $result = $this->saveData($data['data'], $config);
            $result['delete'] = $this->delete($result, $config);
            $config['success']($result);
            $this->info('import success; create ' . count($result['create'])
                . ' rows; update ' . $result['updateCount'] . ' rows; delete '
                . count($result['delete']) . ' rows');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return;
        }
    }

    /**
     * @throws Exception
     */
    protected function resolveConfig(array $config): array
    {
        if (empty($config['model'])) {
            throw new Exception('config field[model] is required');
        }

        $config = array_merge(
            [
                'children' => 'children',
                'unique_fields' => [],
                'unique_fields_separator_default' => '/',
                'unique_fields_separator' => [],
                'custom' => fn() => [],
                'success' => fn() => true,
            ],
            $config,
            [
                'ignore_fields' => $this->option('ignore-fields') ?: ($config['ignore_fields'] ?? []),
            ]
        );

        $config['ignore_fields'] = array_unique($config['ignore_fields']);
        $config['unique_fields'] = array_unique($config['unique_fields']);

        return $config;
    }


    /**
     * @throws Exception
     */
    private function saveData(array $data, array $config, array|Model|null $parent = null): array
    {
        $create = [];
        $update = [];
        $updateCount = 0;

        foreach ($data as $v) {
            $v = $this->resolveUniqueFieldsValue($parent, $v, $config);
            $custom = $config['custom']($v, $parent);
            if ($custom === false) continue;

            $item = array_merge($v, $custom);

            $parentItem = null;
            if ($item['_save'] ?? true) {
                $model = $this->getModel($item, $config);
                if ($model) {
                    $parentItem = $this->update($model, $item, $config);
                    $update[] = $parentItem;
                    if ($parentItem->getChanges()) {
                        $updateCount++;
                    }
                } else {
                    $parentItem = $this->create($item, $config);
                    $create[] = $parentItem;
                }
            } else {
                $parentItem = $item;
            }

            if (isset($item[$config['children']])) {
                $children = $item[$config['children']];
                $result = $this->saveData($children, $config, $parentItem);
                $create = array_merge($create, $result['create']);
                $update = array_merge($update, $result['update']);
            }
        }

        return compact('create', 'update', 'updateCount');
    }

    private function resolveUniqueFieldsValue(array|Model|null $parent, array $data, array $config): array
    {
        if (empty($config['unique_fields'])) return $data;

        if (empty($parent)) return $data;

        foreach ($config['unique_fields'] as $v) {
            if (!isset($data[$v])) throw new Exception('data :' . json_encode($data, JSON_UNESCAPED_UNICODE) . ' unique key[' . $v . '] is not set');
            $separator = $config['unique_fields_separator'][$v] ?? $config['unique_fields_separator_default'];
            $data[$v] = implode($separator, array_filter([$parent[$v] ?? '', $data[$v]]));
        }

        return $data;
    }

    private function getModel(array $data, array $config): Model|null
    {
        if (empty($config['unique_fields'])) return null;

        $where = array_intersect_key($data, array_flip($config['unique_fields']));

        return (new $config['model'])->where($where)->first();
    }

    private function create(array $data, array $config): Model
    {
        return (new $config['model'])->create($data);
    }

    private function update(Model $model, array $data, array $config): Model
    {
        $model->update(Arr::except($data, $config['ignore_fields']));

        return $model;
    }

    private function delete(array $saveData, array $config): array
    {
        if (!$this->option('delete')) return [];

        $model = new $config['model'];
        $keyName = $model->getKeyName();
        $keyNames = array_merge(array_column($saveData['create'], $keyName), array_column($saveData['update'], $keyName));

        $keys = $model->whereNotIn($keyName, $keyNames)->pluck($keyName)->toArray();

        $model->destroy($keys);

        return $keys;
    }
}
