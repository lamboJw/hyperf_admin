<?php

declare (strict_types=1);

namespace App\Model;


class Log extends Model
{
    protected ?string $table = 'op_log';

    protected array $guarded = [];
}
