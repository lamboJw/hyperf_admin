<?php

declare (strict_types=1);

namespace App\Model;


use App\Helper\Api921;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;

class ParentGame extends Model
{
    protected ?string $table = 'parent_game';

    protected array $fillable = ['parent_game_id', 'parent_game_name'];

    #[Cacheable(prefix: 'parent_game', ttl: 86400 * 30, listener: 'flush_parent_game')]
    public function getAll(): array
    {
        return [];
    }

    public function flushCache() {
        $this->getEventDispatcher()->dispatch(new DeleteListenerEvent('flush_parent_game', []));
        $this->getAll();
    }
}
