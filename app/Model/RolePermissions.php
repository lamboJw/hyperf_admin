<?php

declare (strict_types=1);
namespace App\Model;


use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\DbConnection\Db;

class RolePermissions extends Model
{
    protected ?string $table = 'role_permission';


    #[Cacheable(prefix: 'role_permissions', ttl: 86400 * 30, listener: 'flush_role_permissions')]
    public function getRolePermissions($role_id): array
    {
        $cur_permissions = self::query()->where('role_id', $role_id)->pluck('permission_id')->toArray();
        $cur_permissions = implode(',', $cur_permissions);
        // 递归查询获取所有已授权权限及其父权限
        $permission_ids = Db::select("WITH RECURSIVE PermissionPath AS (SELECT id, parent_id FROM permissions WHERE id in ({$cur_permissions}) UNION ALL SELECT p.id, p.parent_id FROM permissions p INNER JOIN PermissionPath pp ON p.id = pp.parent_id) SELECT DISTINCT(id) as id FROM PermissionPath;");
        return array_column($permission_ids, 'id');
    }

    public function flush_role_permissions($role_id) {
        $this->getEventDispatcher()->dispatch(new DeleteListenerEvent('flush_role_permissions', [$role_id]));
    }
}
