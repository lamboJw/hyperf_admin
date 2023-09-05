<?php

declare (strict_types=1);

namespace App\Model;

class Department extends Model
{
    protected ?string $table = 'department';

    public function getDepartmentById(int $id, array $columns = ['*']): array
    {
        $model = self::query()->where('id', $id)->first($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }

    public function getDepartmentByUserId(int $id)
    {
        $model = self::query()->join('users', 'users.department_id', '=', 'department.id')->where('users.id', $id)->first(['department.name']);
        if (empty($model)) {
            return [];
        }
        return $model->toArray()['name'];
    }

    public function truncated()
    {
        self::query()->truncate();
    }

    public function getAll(): array
    {
        $res = [];
        $list = $this->getList(['id', 'name']);
        foreach ($list as $v) {
            $res[$v['id']] = $v['name'];
        }
        return $res;
    }
}
