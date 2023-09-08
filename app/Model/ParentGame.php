<?php

declare (strict_types=1);

namespace App\Model;


use App\Helper\Api921;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

class ParentGame extends Model
{
    protected ?string $table = 'parent_game';

    protected array $fillable = ['parent_game_id', 'parent_game_name'];

    const CACHE_KEY = 'cs:parent_game';

    #[Inject]
    protected Redis $redis;

    public function getAll(): array
    {
        $list = $this->redis->hGetAll(self::CACHE_KEY);
        if (!empty($list)) {
            return $list;
        }
        $parent_game = $this->getContainer()->get(Api921::class)->get_parent_game();
        if (!$parent_game) return [];
        $data = [];
        foreach ($parent_game as $item) {
            $data[] = ['parent_game_id' => $item['game_code'], 'parent_game_name' => $item['game_name']];
        }
        self::query()->insertOrIgnore($data);
        $list = array_column($parent_game, 'game_name', 'game_code');
        $this->redis->hMSet(self::CACHE_KEY, $list);
        return $list;
    }

    public function getOne($parent_game_id): string
    {
        return $this->redis->hGet(self::CACHE_KEY, $parent_game_id) ?: '';
    }

    public function getMany($pgids): array
    {
        return $this->redis->hMGet(self::CACHE_KEY, $pgids);
    }

    public function flushCache()
    {
        $this->redis->del(self::CACHE_KEY);
        $this->getAll();
    }
}
