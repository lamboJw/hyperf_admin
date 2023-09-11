<?php


namespace App\Service;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Permissions;
use App\Model\RolePermissions;
use App\Service\Interfaces\PermissionsServiceInterface;
use App\Service\Interfaces\TokenServiceInterface;
use Hyperf\Di\Annotation\Inject;
use JetBrains\PhpStorm\ArrayShape;

class PermissionsService extends AbstractService implements PermissionsServiceInterface
{

    #[Inject]
    protected Permissions $model;

    #[Inject]
    protected TokenServiceInterface $tokenService;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createPermission(array $data): int
    {
        $re = $this->model::query()->create($data);
        return $re->id;
    }

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getPermissionById(int $id, array $columns = ['*']): array
    {
        return $this->model->find($id, $columns)?->toArray();
    }

    /**
     * 多条
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 权限集合。排序好的权限集合
     */
    public function getPermissionList(array $where = [], array $columns = ['id', 'name', 'parent_id', 'tag', 'sort', 'icon', 'path'], array $options = ['orderBy' => 'sort']): array
    {
        $permissionList = $this->model->buildWhere($this->model::query(), $where, $options)->select($columns)->get()->toArray();
        $parent_list = [];
        foreach ($permissionList as $permission) {
            $parent_list[$permission['parent_id']][] = $permission;
        }
        return $this->buildPermissionTree(0, $parent_list, $options['get_routes'] ?? false);
    }

    /**
     * 生成多级权限树
     * @param int $parent_id 上级id，最高级为0
     * @param array $permission_list 根据上级id分组的权限列表
     * @param bool $get_routes 是否获取路由信息
     * @return array
     */
    public function buildPermissionTree(int $parent_id, array &$permission_list, bool $get_routes = false): array
    {
        $permissions = $permission_list[$parent_id] ?? [];
        foreach ($permissions as &$permission) {
            if($get_routes) {
                if($parent_id == 0) {
                    $permission['redirect'] = 'noRedirect';
                    $permission['alwaysShow'] = true;
                    $permission['component'] = 'Layout';
                } else {
                    $permission['component'] = $permission['path'];
                }
                $permission['meta'] = [
                    'permission' => $permission['tag'],
                    'title' => $permission['name'],
                    'icon' => $permission['icon'],
                ];
                $permission['name'] = $permission['tag'];
                unset($permission['tag'], $permission['icon']);
            }
            $children = $this->buildPermissionTree($permission['id'], $permission_list, $get_routes);
            if (!empty($children)) {
                $permission['children'] = $children;
            }
        }
        unset($permission);
        return $permissions;
    }


    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updatePermissionById(int $id, array $data): int
    {
        return $this->model::query()->where('id', $id)->update($data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     */
    public function deletePermission(int $id)
    {
        // 获取当前权限
        $permission = $this->getPermissionById($id);
        if (!$permission) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS, '找不到该权限');
        }
        // 判断该权限是否绑定了角色，如果是的话则不允许直接删除权限
        $rolePermission = RolePermissions::query()
            ->select('permission_id', 'role_id')
            ->where('permission_id', $id)
            ->get()
            ->toArray();
        if (!empty($rolePermission)) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS, $permission['name'] . ' 权限绑定了角色，不能直接删除');
        }
        $subPermissions = $this->getSubPermissions($permission['id']);
        if (!empty($subPermissions)) {
            // 否则删除该节点的所有子节点
            foreach ($subPermissions as $subPermission) {
                $this->deletePermission($subPermission['id']);
            }
        }
        $this->model::query()->where('id', $id)->delete();
    }


    /**
     * 查询多条 - 根据ID.
     * @param int $parentId 父节点的 id
     * @param string[] $columns 查询字段
     * @return array 数组
     */
    private function getSubPermissions(int $parentId, array $columns = ['*']): array
    {
        return $this->model::query()->where('parent_id', $parentId)->select($columns)->get()->toArray();
    }


    public function checkParentGamePermission(string $type, int $pgid = null): bool|array
    {
        $dp = $this->tokenService->getDataPermission($type);
        if ($dp['data_permission'] != 1) {  // 不是可查看所有数据
            if ($dp['data_permission'] == 3 && !empty($dp['parent_game'])) {   // 指定可见数据，且选择了一级游戏
                if (!empty($pgid) && !in_array($pgid, $dp['parent_game'])) { // 检查pgid是否在已选列表
                    throw new BusinessException(ErrorCode::PARENT_GAME_NOT_AUTH);
                } else {    //返回具体游戏权限
                    return $dp['parent_game'];
                }
            } else {  // 指定可见数据，且没有选择一级游戏，或仅与自己相关数据
                if ($type == 'parent_game') {   // 全局游戏权限，返回空权限
                    if (!empty($pgid)) { //如果有指定pgid，就是要检查pgid是否有权限，所以抛出异常
                        throw new BusinessException(ErrorCode::PARENT_GAME_NOT_AUTH);
                    } else {
                        return $dp['parent_game'];
                    }
                } else {    // 细分权限中，判断全局游戏权限
                    return $this->checkParentGamePermission('parent_game', $pgid);
                }
            }
        } else {    //返回true代表全部可见
            return true;
        }
    }
}
