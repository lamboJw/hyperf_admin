<?php


namespace App\Service;


use App\Model\Permissions;
use App\Model\RoleDataPermissions;
use App\Model\RolePermissions;
use App\Model\Roles;
use App\Model\UserRole;
use App\Service\Interfaces\RolesServiceInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use App\Model\Users;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class RolesService extends AbstractService implements RolesServiceInterface
{
    #[Inject]
    protected Roles $model;

    #[Inject]
    protected TokenService $tokenService;

    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    #[Inject]
    protected RoleDataPermissions $roleDataPermissions;

    #[Inject]
    protected RolePermissions $rolePermissions;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRole(array $data): int
    {
        try {
            // 新增角色、以及角色和权限的对应关系
            return Db::transaction(function () use ($data) {
                $permissionIds = $data['permission_ids'];
                unset($data['permission_ids']);
                $data_permission = $data['data_permission'] ?? [];
                unset($data['data_permission']);
                $data['created_user_id'] = $this->tokenService->getUid();
                $re = $this->model::query()->create($data);
                $roleId = $re->id;
                $rolePermission = [];
                foreach ($permissionIds as $permissionId) {
                    $rolePermission[] = ['role_id' => $roleId, 'permission_id' => $permissionId];
                }
                $this->rolePermissions::query()->insert($rolePermission);
                $this->rolePermissions->flush_role_permissions($roleId);
                $this->roleDataPermissions->saveDataPermission($data_permission, $roleId);
                return $roleId;
            });
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * 多条
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 权限集合。排序好的权限集合
     */
    public function getRoleList(array $where, array $columns = ['*'], array $options = []): array
    {
        $res = $this->model->getPageList($where, $columns, $options);
        if (empty($res) || empty($res['data'])) {
            return [];
        }
        $role_ids = array_column($res['data'], 'id');
        $role_has_users = UserRole::query()->join('users', 'users.id', '=', 'user_role.user_id')->whereIn('user_role.role_id', $role_ids)->where('users.status', 1)->selectRaw('user_role.role_id,GROUP_CONCAT(users.name SEPARATOR "、") as role_has_users')->groupBy(['user_role.role_id'])->pluck('role_has_users', 'role_id')->toArray();
        $creator_ids = array_column($res['data'], 'created_user_id');
        $creator_list = Users::query()->whereIn('id', $creator_ids)->select(['id', 'name'])->get()->toArray();
        $creator_list = array_column($creator_list, 'name', 'id');
        foreach ($res['data'] as &$role) {
            $role['created_user_name'] = $creator_list[$role['created_user_id']] ?? '未知';
            $role['role_has_users'] = $role_has_users[$role['id']] ?? '';
        }
        return $res;
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoleById(int $id, array $data): int
    {
        try {
            // 更新角色、以及角色和权限的对应关系
            return Db::transaction(function () use ($id, $data) {
                $permissionIds = $data['permission_ids'];
                unset($data['permission_ids']);
                $data_permission = $data['data_permission'] ?? [];
                unset($data['data_permission']);
                // 先更新角色表信息
                if (!empty($data)) {
                    $this->model::query()->where('id', $id)->update($data);
                }

                // 删除原有角色和权限的映射关系
                $this->rolePermissions::query()->where('role_id', $id)->delete();
                // 再将现有的角色和权限的关系插入库中
                $rolePermission = [];
                foreach ($permissionIds as $permissionId) {
                    $rolePermission[] = ['role_id' => $id, 'permission_id' => $permissionId];
                }
                $this->rolePermissions::query()->insert($rolePermission);
                $this->rolePermissions->flush_role_permissions($id);
                // 删除原有角色和数据权限的映射关系
                RoleDataPermissions::query()->where('role_id', $id)->delete();
                // 再将现有的角色和数据权限的关系插入库中
                $this->roleDataPermissions->saveDataPermission($data_permission, $id);
                return $id;
            });
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRole(int $id): int
    {
        return Db::transaction(function () use ($id) {
            // 删除角色信息
            $deleteRes = $this->model::query()->where('id', $id)->delete();
            // 删除角色和权限的映射信息
            RolePermissions::query()->where('role_id', $id)->delete();
            RoleDataPermissions::query()->where('role_id', $id)->delete();
            return $deleteRes;
        });
    }

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getRoleById(int $id, array $columns = ['*']): array
    {
        $role = $this->model::query()->find($id, $columns)?->toArray();
        if (empty($role)) {
            return [];
        }

        // 先查询该角色是否绑定了权限信息
        $permissionIds = RolePermissions::query()->where('role_id', $id)->pluck('permission_id')->toArray();
        if (empty($permissionIds)) {
            $role['permissions'] = [];
            $role['permission_ids'] = [];
        } else {
            $permission_list = Permissions::query()->whereIn('id', $permissionIds)
                ->select('tag', 'id')->get()->toArray();
            $role['permissions'] = array_column($permission_list, 'tag');
            $role['permission_ids'] = array_column($permission_list, 'id');
        }
        $role_has_users = UserRole::query()->join('users', 'users.id', '=', 'user_role.user_id')->join('department', 'users.department_id', '=', 'department.id')->where('users.status', 1)->where('role_id', $id)->select('users.username', 'users.name', 'department.name as department_name', 'users.position')->get()->toArray();
        $role['role_has_users'] = !empty($role_has_users) ? $role_has_users : [];
        $dataPermissions = $this->roleDataPermissions::query()->where('role_id', $id)->select($this->roleDataPermissions->getFillable())->get()->toArray();
        if (!empty($dataPermissions)) {
            $role['data_permission'] = $dataPermissions;
            foreach ($role['data_permission'] as &$value) {
                $value = (array)$value;
                if ($value['data_permission'] == 0) {
                    $value['data_permission'] = '';
                }
                $this->roleDataPermissions->handleDataPermission($value);
            }
            unset($value);
            $role['data_permission'] = array_column($role['data_permission'], null, 'data_type');
        } else {
            $role['data_permission'] = new \ArrayObject();
        }
        return $role;
    }

    /**
     * 获取全部角色信息，不分页
     */
    public function getAllRole(array $where = [], array $columns = ['*']): array
    {
        return $this->model::query()->get()->toArray();
    }
}
