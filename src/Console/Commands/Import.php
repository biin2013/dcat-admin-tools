<?php

namespace Biin2013\DcatAdminTools\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:import
                        { path : file path, start with config_path/imports  },
                        { --E|except=* : except update fields },
                        { --O|only=* : only update fields },
                        { --T|truncate : truncate table }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = <<<STR
    import data to databases, file format:
    [
        'config' => [
            // table name, if empty, default is file name
            'table' => 'admin_settings',
            // primary_key field, default key field
            'primary_key' => ['key'],
            // mapping parent key to field, default empty, will not be mapping parent key
            'mapping_parent_key' => '',
            // parent key separator, default /
            'parent_key_separator' => '/',
        ],
        'data' => [
            [
                'key' => '',
                'value' => '',
                'type' => 'bool|float|int|string|json|array',
                'brief' => ''
            ],
            ...
        ]
        // or data is
        'data' => [
            [
                'parent_key' => '',
                'children' => [
                    [
                        'key' => ...
                    ],
                    ...
                ]
            ],
            ...
        ]
    ]
STR;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->info('start import');
        $path = config_path('imports/' . $this->argument('path') . '.php');
        $data = require $path;
        $config = $this->resolveConfig($data['config'] ?? []);

        if (empty($data['data'])) {
            $this->error('data field required');
            return;
        }

        $exceptFields = $this->option('except');
        if (empty($exceptFields)) {
            $result = $this->choice('please select except fields: ', [
                'value',
                ''
            ], 0);
            if ($result) {
                $this->info('selected: value');
                $exceptFields[] = $result;
            }
        }
        if (empty($exceptFields) && !$this->confirm('empty except fields, are you sure ?', true)) {
            $this->warn('user terminal');
            return;
        }

        $this->insertToDb($config, $data['data'], $exceptFields);
        $this->info('import success');
    }

    private function resolveConfig(array $config): array
    {
        $config = array_merge([
            'primary_key' => ['key'],
            'mapping_parent_key' => '',
            'parent_key_separator' => '/',
        ], $config);

        if (empty($config['table'])) {
            $config['table'] = basename($this->argument('path'));
        }

        return $config;
    }

    /**
     * @throws Exception
     */
    private function insertToDb(array $config, array $data, array $exceptFields): void
    {
        $data = $this->resolveData(
            $config,
            $data
        );

        if ($this->option('truncate')) {
            DB::table($config['table'])->truncate();
            DB::table($config['table'])->insert($data);
        } else {
            DB::beginTransaction();
            try {
                foreach ($data as $v) {
                    DB::table($config['table'])
                        ->updateOrInsert(
                            array_combine($config['primary_key'], array_map(fn($val) => $v[$val], $config['primary_key'])),
                            array_diff_key($v, array_flip($exceptFields))
                        );
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }

    /**
     * @throws Exception
     */
    private function resolveData(
        array $config,
        array $data,
        array $parentKeys = []
    ): array
    {
        $list = [];

        foreach ($data as $v) {
            if (empty($v['children'])) {
                if ($config['mapping_parent_key'] && !empty($parentKeys)) {
                    $v[$config['mapping_parent_key']] = implode($config['parent_key_separator'], $parentKeys);
                }
                $list[] = $v;
            } else {
                if (empty($v['parent_key'])) {
                    throw new Exception('parent_key field required');
                }
                $list = array_merge(
                    $list,
                    $this->resolveData(
                        $config,
                        $v['children'],
                        array_merge($parentKeys, [$v['parent_key']])
                    )
                );
            }
        }

        return empty($this->option('only'))
            ? $list
            : array_intersect_key($list, array_flip($this->option('only')));
    }
}