<?php


namespace App\Service\Interfaces;


use Hyperf\Contract\LengthAwarePaginatorInterface;

interface DepartmentServiceInterface {

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array
     */
    public function getDepartmentList(array $where, array $columns = ['*'], array $options = []): array;


    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createDepartment(array $data): int;



    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateDepartmentById(int $id, array $data): int;


    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteDepartment(int $id): int;



}
