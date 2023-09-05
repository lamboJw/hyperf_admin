<?php

declare (strict_types=1);

namespace App\Model;


use Hyperf\Cache\Annotation\Cacheable;

class OpAction extends Model
{
    protected ?string $table = 'op_action';

    #[Cacheable(prefix: 'op_action', ttl: 86400 * 30, listener: 'flush_op_action')]
    public function get_action($path): array|null
    {
        return self::query()->where('path', $path)->first()?->toArray();
    }
}
