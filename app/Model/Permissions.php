<?php

declare (strict_types=1);
namespace App\Model;


class Permissions extends Model
{
    protected ?string $table = 'permissions';

    protected array $fillable = ['name', 'path', 'parent_id', 'tag', 'sort', 'icon'];
}
