<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\Paginator\LengthAwarePaginator;

abstract class Model extends BaseModel
{
    public function buildWhere($model, array $where, array $options = []): Builder
    {
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                ## 一维数组
                if (!is_array($v)) {
                    $model = $model->where($k, $v);
                    continue;
                }

                ## 二维索引数组
                if (is_numeric($k)) {
                    $v[1] = mb_strtoupper($v[1]);
                    $boolean = $v[3] ?? 'and';
                    if (in_array($v[1], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'])) {
                        $model = $model->where($v[0], $v[1], $v[2], $boolean);
                    } elseif ($v[1] == 'IN') {
                        $model = $model->whereIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'NOT IN') {
                        $model = $model->whereNotIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'RAW') {
                        $model = $model->whereRaw($v[0], $v[2] ?? null, $boolean);
                    } elseif ($v[1] == 'BETWEEN') {
                        $model = $model->whereBetween($v[0], $v[2], $boolean);
                    }
                } else {
                    ## 二维关联数组
                    $model = $model->whereIn($k, $v);
                }
            }
        }

        ## 排序
        isset($options['orderByDesc']) && $model = $model->orderByDesc($options['orderByDesc']);
        isset($options['orderBy']) && $model = $model->orderBy($options['orderBy']);
        isset($options['orderByRaw']) && $model = $model->orderByRaw($options['orderByRaw']);
        return $model;
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getPageList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->buildWhere(self::query(), $where, $options)->select($columns)->paginate($options['prePage'] ?? 15, ['*'], 'page', $options['page'] ?? 1)->toArray();
    }

}
