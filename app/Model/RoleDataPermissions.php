<?php

declare (strict_types=1);
namespace App\Model;


use App\Service\Interfaces\WorkWechatServiceInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;

class RoleDataPermissions extends Model
{
    protected ?string $table = 'role_data_permission';

    protected array $fillable = ['role_id', 'data_type', 'data_permission', 'parent_game', 'department'];

    public function saveDataPermission($data_permission, $role_id) {
        $dataPermission = [];
        foreach ($data_permission as $key => $value) {
            if ($role_id == 1) {
                $value['data_permission'] = 1;  //超管强制查看全部
            }
            $value['role_id'] = $role_id;
            $value['data_type'] = $key;
            $value['parent_game'] = !empty($value['parent_game']) ? implode(',', $value['parent_game']) : '';
            $value['department'] = !empty($value['department']) ? json_encode($value['department'], JSON_UNESCAPED_UNICODE) : '';
            $dataPermission[] = $value;
            $this->getEventDispatcher()->dispatch(new DeleteListenerEvent('dataPermissions', [$role_id, $key]));
        }
        if (!empty($dataPermission)) {
            RoleDataPermissions::query()->insert($dataPermission);
        }
    }

    public function handleDataPermission(&$dp) {
        $dp['parent_game'] = !empty($dp['parent_game']) ? explode(',', $dp['parent_game']) : [];
        $dp['department'] = !empty($dp['department']) ? json_decode($dp['department'], true) : [];
    }

    /**
     * 获取角色数据权限
     * @param $role_id
     * @param $type
     * @return array
     */
    #[Cacheable(prefix: "dataPermissions", ttl: 86400, listener: "dataPermissions")]
    public function dataPermission($role_id, $type): array
    {
        $dataPermission = self::query()
            ->where('role_id', $role_id)->where('data_type', $type)
            ->select('data_permission', 'parent_game', 'department')
            ->first();
        if (empty($dataPermission)) {
            $dataPermission = ['data_permission' => 2];
        } else {
            $dataPermission = $dataPermission->toArray();
            $this->handleDataPermission($dataPermission);
            if (!empty($dataPermission['department'])) {
                $wc_uid = [];
                $workWechat = $this->getContainer()->get(WorkWechatServiceInterface::class);
                foreach ($dataPermission['department'] as $item) {
                    if (count($item) == 1) {
                        $users = $workWechat->user($item[0]);
                        $wc_uid = array_merge($wc_uid, array_column($users, 'userid'));
                    } else {
                        $wc_uid[] = $item[1];
                    }
                }
                $wc_uid = array_unique($wc_uid);
                $dataPermission['department'] = WechatUser::query()->whereIn('wc_uid', $wc_uid)->pluck('uid')->toArray();
            }
        }
        return $dataPermission;
    }
}
