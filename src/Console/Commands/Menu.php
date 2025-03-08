<?php

namespace Biin2013\DcatAdminTools\Console\Commands;

use Dcat\Admin\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class Menu extends Command
{
    protected $signature = 'admin:menu
                            { --P|path= : The path of menu file }';

    protected $description = 'Generate or update menu';


    /**
     *  true 显示菜单
     *  false 不显示菜单
     */
    private bool $show = true;

    /**
     * true 使用键值作为URI路径
     * false 不使用键值作为URI路径
     */
    private bool $uri = true;

    /**
     * true 写入权限
     * false 不写入权限
     */
    private bool $permission = true;

    private array $icons = [
        '',
        'feather icon-grid',
        'feather icon-align-justify',
        'feather icon-menu',
        'feather icon-sidebar',
        'feather icon-align-left'
    ];

    private string $menuTable;

    private string $permissionTable;

    private string $permissionMenuTable;

    private string $rolePermissionTable;

    private string $menuModel;

    private string $createdAt;

    private array $menuIds = [];

    private array $permissionIds = [];

    private string $routePrefix;

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->info('Generate menu');


        $this->menuTable = config('admin.database.menu_table');
        $this->permissionTable = config('admin.database.permissions_table');
        $this->permissionMenuTable = config('admin.database.permission_menu_table');
        $this->rolePermissionTable = config('admin.database.role_permissions_table');
        $this->menuModel = config('admin.database.menu_model');
        $this->createdAt = date('Y-m-d H:i:s');
        $this->routePrefix = app(Application::class)->getRoutePrefix();

        DB::transaction(fn() => $this->generate());

        $this->info('Generate menu succeed');
        $this->warn('please clear menu cache');
    }

    private function generate(): void
    {
        $data = $this->option('path') ?? config('admin_menu');

        if (empty($data)) {
            $this->error('Menu is empty');
            return;
        }

        $this->saveData($data);
        $this->clear();
    }

    private function saveData(
        array  $data,
        int    $parentId = 0,
        string $parentUri = '',
        int    &$menuOrder = 0,
        int    $permissionParentId = 0,
        int    &$permissionOrder = 0,
        int    $level = 1
    ): void
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
                $value = [];
            }
            $value = array_merge($this->defaultOperationFields(), $value);

            $menuOrder++;
            $menuData = $this->insertOrUpdateMenu(
                $key,
                $value,
                $parentId,
                $parentUri,
                $menuOrder,
                $level
            );

            $permissionId = $permissionParentId;
            if ($value['_permission'] ?? $this->permission) {
                $permissionOrder++;
                $permissionData = $this->insertOrUpdatePermission(
                    $key,
                    $permissionParentId,
                    $parentUri,
                    $permissionOrder
                );
                $permissionId = $permissionData['id'];
            }


            if ($value['children'] ?? null) {
                $this->saveData(
                    $value['children'],
                    $menuData['id'],
                    $menuData['uri'],
                    $menuOrder,
                    $permissionId,
                    $permissionOrder,
                    $level + 1
                );
            } else {
                $this->relationPermissionMenu($menuData['id'], $permissionId);

                if (!empty($value['operations']) && ($value['_permission'] ?? $this->permission)) {
                    $this->insertOrUpdatePermissionOperations(
                        $value['operations'],
                        $permissionId,
                        $menuData['uri'],
                        $permissionOrder
                    );
                }
            }
        }
    }

    private function defaultOperationFields(): array
    {
        return ['operations' => ['index', 'store', 'update', 'destroy']];
    }

    private function insertOrUpdateMenu(
        string $title,
        array  $data,
        int    $parentId,
        string $parentUri,
        int    $order,
        int    $level
    ): array
    {
        $data = $this->resolveMenuData(
            $title,
            $data,
            $parentId,
            $parentUri,
            $order,
            $level
        );
        $id = $this->insertMenuGetId($data);

        $data['id'] = $id;
        $this->menuIds[] = $id;

        return $data;
    }

    private function resolveMenuData(
        string $title,
        array  $data,
        int    $parentId,
        string $parentUri,
        int    $order,
        int    $level
    ): array
    {
        $uri = ($data['_uri'] ?? $this->uri)
            ? $parentUri . '/' . $title
            : $parentUri;
        $icon = $data['_icon'] ?? $this->icons[$level] ?? '';
        $show = $data['_show'] ?? $this->show;

        return [
            'parent_id' => $parentId,
            //'title' => $data['title'] ?? str_replace('/', '_', ltrim($uri, '/')),
            'title' => $uri,
            'uri' => $uri,
            'icon' => $icon,
            'order' => $order,
            'show' => $show,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];
    }

    private function insertMenuGetId(array $data): int
    {
        DB::table($this->menuTable)->updateOrInsert(
            ['uri' => $data['uri']],
            $data
        );

        return DB::table($this->menuTable)->where('uri', $data['uri'])->value('id');
    }

    private function insertOrUpdatePermission(
        string $title,
        int    $parentId,
        string $parentUri,
        int    $order
    ): array
    {
        $data = $this->resolvePermissionData(
            $title,
            $parentId,
            $parentUri,
            $order
        );

        $id = $this->insertPermissionGetId($data);

        $data['id'] = $id;
        $this->permissionIds[] = $id;

        return $data;
    }

    private function resolvePermissionData(
        string $title,
        int    $parentId,
        string $parentUri,
        int    $order
    ): array
    {
        $slug = $parentUri . '/' . $title;
        $path = $this->routePrefix . ltrim(str_replace('/', '.', $slug), '.');

        return [
            'name' => $title,
            'slug' => $slug,
            'http_path' => $path,
            'parent_id' => $parentId,
            'order' => $order,
            'created_at' => $this->createdAt,
            'updated_at' => $this->createdAt,
        ];
    }

    private function insertPermissionGetId(array $data): int
    {
        DB::table($this->permissionTable)
            ->updateOrInsert(
                ['slug' => $data['slug']],
                $data
            );

        return DB::table($this->permissionTable)->where('slug', $data['slug'])->value('id');
    }

    private function relationPermissionMenu(
        int $menuId,
        int $permissionId
    ): void
    {
        app($this->menuModel)->find($menuId)->permissions()->sync([$permissionId + 1]);
    }

    private function insertOrUpdatePermissionOperations(
        array  $operations,
        int    $parentId,
        string $parentUri,
        int    &$order
    ): void
    {
        if (!in_array('index', $operations, true)) {
            array_unshift($operations, 'index');
        }

        foreach ($operations as $value) {
            $order++;
            $this->insertOrUpdatePermission(
                $value,
                $parentId,
                $parentUri,
                $order
            );
        }
    }

    private function clear(): void
    {
        $this->clearMenus();
        $this->clearPermissions();
        $this->clearPermissionMenu();
        $this->clearRolePermissions();
        $this->clearMenuCache();
    }

    private function clearMenus(): void
    {
        DB::table($this->menuTable)->whereNotIn('id', $this->menuIds)->delete();
    }

    private function clearPermissions(): void
    {
        DB::table($this->permissionTable)->whereNotIn('id', $this->permissionIds)->delete();
    }

    private function clearPermissionMenu(): void
    {
        DB::table($this->permissionMenuTable)->whereNotIn('menu_id', $this->menuIds)->delete();
    }

    private function clearRolePermissions(): void
    {
        DB::table($this->rolePermissionTable)->whereNotIn('permission_id', $this->permissionIds)->delete();
    }

    private function clearMenuCache(): void
    {
        app($this->menuModel)->flushCache();
    }
}
