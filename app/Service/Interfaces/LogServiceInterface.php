<?php


namespace App\Service\Interfaces;


interface LogServiceInterface
{

    /**
     * 多条分页.
     * @param array $params 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getLogList(array $params, array $columns = ['*'], array $options = []): array;


}
