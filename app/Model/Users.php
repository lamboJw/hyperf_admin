<?php
/**
 * @Auther : zhengxiaokai
 * @Date : 2021/06/11/3:58 下午
 * @Description:
 */


namespace App\Model;


class Users extends Model
{

    protected ?string $table = "users";

    protected array $fillable = ['username', 'name', 'password', 'email', 'department_id', 'position', 'status', 'last_login'];

    public function department(): \Hyperf\Database\Model\Relations\HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function getUserById(int $id, array $columns = ['*']): array
    {
        $model = self::query()->where([['id', '=', $id], ['status', '=', 1]])->first($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }


    /**
     * 获取所有当前部门的所有下属员工信息
     */
    public function getAllBranch(int $departmentId, array $columns = ['*']): array
    {
        $where = [
            ['department_id', '=', $departmentId],
            ['position', '=', 1],
            ['status', '=', 1],
        ];
        return self::query()->where($where)->select($columns)->get()->toArray();
    }


    /**
     * 根据权限获取用户
     * @param int $permissionId 权限ID
     * @param array $columns 获取的用户的列
     * @return array 用户数据
     */
    public function getUserByPermission(int $permissionId, array $columns = ['*']): array
    {
        $model = self::query()
            ->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('permissions.id', $permissionId)
            ->where('users.status', 1)
            ->get($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }


    public function getRoleInfoByUserId($id): array
    {
        $columns = ['roles.*'];
        $where[] = ['users.status', '=', 1];
        $where[] = ['roles.status', '=', 1];
        $model = self::query()
            ->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('users.id', $id)
            ->first($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }


    public function getUsersTree($check_status = true): array
    {
        $where = [];
        if ($check_status) {
            $where[] = ['users.status', '=', 1];
        }
        $columns = ['department.name as department_name', 'users.id', 'users.name'];
        $model = self::query()
            ->join('department', 'users.department_id', '=', 'department.id')
            ->where($where)
            ->get($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }

    public function getIdUsers(): array
    {
        return self::query()->where('status', 1)->pluck('name', 'id')->toArray();
    }

    public function getIdDepartUser($check_status = true): array
    {
        $user = [];
        $data = $this->getUsersTree($check_status);
        foreach ($data as $v) {
            $user[$v['id']] = $v;
        }
        return $user;
    }

}
