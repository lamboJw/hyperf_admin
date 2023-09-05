<?php


namespace App\Service\Interfaces;


interface RolesServiceInterface {


    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getRoleById(int $id, array $columns = ['*']):array ;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRole(array $data): int;


    /**
     * 多条
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 权限集合。排序好的权限集合
     */
    public function getRoleList(array $where, array $columns = ['*'], array $options = []): array;


    /**
     * 获取全部角色信息，不分页
     */
    public function getAllRole(array $where = [], array $columns = ['*']):array ;


    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoleById(int $id, array $data): int;


    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRole(int $id): int;
}
