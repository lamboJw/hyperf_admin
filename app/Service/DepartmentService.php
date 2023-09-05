<?php

namespace App\Service;

use App\Model\Department;
use App\Service\Interfaces\DepartmentServiceInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\Di\Annotation\Inject;

class DepartmentService extends AbstractService implements DepartmentServiceInterface
{

    #[Inject]
    protected Department $model;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array
     */
    public function getDepartmentList(array $where, array $columns = ['*'], array $options = []): array
    {
        return  $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createDepartment(array $data): int
    {
        $re = $this->model::query()->create($data);
        return $re->id;
    }


    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateDepartmentById(int $id, array $data): int
    {
        return $this->model::query()->where('id', $id)->update($data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteDepartment(int $id): int
    {
        return $this->model::query()->where('id', $id)->delete();
    }
}
