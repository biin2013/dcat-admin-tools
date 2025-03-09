<?php

namespace Biin2013\DcatAdminTools\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:import
                        { path : file path, start with lang_path/console/import  },
                        { --E|except=* : except update fields },
                        { --O|only=* : only update fields },
                        { --T|truncate : truncate table }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import data to databases';

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
        $this->info('start handle');
        $path = 'console/import/' . $this->argument('path');
        $config = Lang::get($path);
        if (!is_array($config)) {
            $this->error(lang_path($path) . ' file not exist');
            return;
        }
        if (empty($config['table'])) {
            $this->error('table field required');
            return;
        }

        if (empty($config['data'])) {
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
        if (empty($exceptFields) && !$this->confirm('empty except fields, are you sure ?')) {
            $this->warn('user terminal');
            return;
        }

        $this->insertToDb($config, $exceptFields);
        $this->info('end handle');
    }

    private function insertToDb($config, $exceptFields): void
    {
        $primaryKey = $config['unique'] ?? 'key';
        $data = $this->resolveData($config['data'], $primaryKey);

        if ($this->option('truncate')) {
            DB::table($config['table'])->truncate();
            DB::table($config['table'])->insert($data);
        } else {
            $exists = DB::table($config['table'])
                ->whereIn($primaryKey, array_keys($data))
                ->pluck($primaryKey)
                ->toArray();
            foreach ($data as $v) {
                if (in_array($v[$primaryKey], $exists)) {
                    DB::table($config['table'])
                        ->where($primaryKey, $v[$primaryKey])
                        ->update(array_diff_key($v, array_flip($exceptFields)));
                } else {
                    DB::table($config['table'])->insert($v);
                }
            }
        }
    }

    private function resolveData(array $data, string $primaryKey): array
    {
        $list = [];
        $only = $this->option('only');
        foreach ($data as $v) {
            if (empty($v['children'])) {
                $list[$v[$primaryKey]] = $v;
            } else {
                $list = array_merge($list, $this->resolveData($v['children'], $primaryKey));
            }
        }

        return empty($only)
            ? $list
            : array_intersect_key($list, array_flip($only));
    }
}