<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Context\Context;

/* socket 服务 */
class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    public function onMessage($server, $frame): void
    {
        /* 暂无使用逻辑 */
        $data_arr = json_decode($frame->data, true);
        if (is_array($data_arr)) {
            if ($data_arr['action'] == 'contract_viewing') {
                Context::set('Authorization', "Bearer {$data_arr['token']}");
            }
        } else {
            $server->push($frame->fd, $frame->data);
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {

    }

    public function onOpen($server, $request): void
    {
        switch ($request->get['opt']){
            case 'trigger'://消息盒子即时通知 - 后端触发通知
                break;
            case 'login'://消息盒子 - 用户建立链接，创建 uid 与 fd 关联
                break;
            default:
                break;
        }
    }

}
