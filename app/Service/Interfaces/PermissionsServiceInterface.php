<?php


namespace App\Service\Interfaces;



interface PermissionsServiceInterface {

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createPermission(array $data): int;


    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getPermissionById(int $id, array $columns = ['*']):array ;


    /**
     * 多条
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 权限集合。排序好的权限集合
     */
    public function getPermissionList(array $where = [], array $columns = ['id', 'name', 'parent_id', 'tag', 'sort', 'icon', 'path'], array $options = ['orderBy' => 'sort']): array;

    /**
     * 生成多级权限树
     * @param int $parent_id 上级id，最高级为0
     * @param array $permission_list 根据上级id分组的权限列表
     * @param bool $get_routes 是否获取路由信息
     * @return array
     */
    public function buildPermissionTree(int $parent_id, array &$permission_list, bool $get_routes = false): array;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updatePermissionById(int $id, array $data): int;


    /**
     * 删除 - 单条
     * @param int $id 删除ID
     */
    public function deletePermission(int $id);

}
