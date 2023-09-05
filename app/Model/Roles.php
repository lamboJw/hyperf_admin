<?php

declare (strict_types=1);

namespace App\Model;

class Roles extends Model
{
    protected ?string $table = 'roles';

    protected array $fillable = ['name', 'created_user_id', 'desc'];

    public function getPermissionInfoByRoleId($id, $columns = ['permissions.*']): array
    {
        $where[] = ['roles.id', '=', $id];
        $model = self::query()
            ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where($where)
            ->get($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }
}
