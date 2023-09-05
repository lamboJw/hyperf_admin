<?php


namespace App\Event\Listener;


use App\Event\RequestDone;
use App\Model\Log;
use App\Model\OpAction;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Phper666\JWTAuth\Util\JWTUtil;

#[Listener]
class RequestDoneListener implements ListenerInterface
{

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected Log $log;

    #[Inject]
    protected OpAction $opAction;


    private array $operation = [
        "/user/login" => "登录账号",
        "/user/logout" => "登出账号",
        "/user/refresh" => "刷新 token",
    ];


    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            RequestDone::class
        ];
    }


    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        $uri = $this->request->getRequestUri();
        $res = $event->res;
        $op = '';
        $name = '';
        $status = -1;
        if (array_key_exists($uri, $this->operation)) {
            $op = $this->operation[$uri];
            if ($res['code'] == 0) {
                // 登录相关操作成功
                $token = $res['result']['token'];
                $tokenObj = JWTUtil::getParser()->parse($token);
                $name = $tokenObj->claims()->get("name");
                $status = 1;
            } else {
                // 登录失败不记录
                return;
            }
        } else {
            // 从操作行为表获取数据
            $action = $this->opAction->get_action($uri);
            // 没在操作行为表中的则不记录
            if (empty($action)) {
                return;
            }
            $op = $action['name'];

            // 获取 jwt token
            $token = $this->request->getHeaderLine('Authorization') ?? '';
            if ($token) {
                $token = JWTUtil::handleToken($token);
                $tokenObj = JWTUtil::getParser()->parse($token);
                $name = $tokenObj->claims()->get("name");
                // 操作状态
                if ($res['code'] == 0) {
                    $status = 1;
                    if (!empty($action['desc'])) {  //如果有详细描述，把详细描述添加到操作内容后面
                        if (preg_match_all('/\{(.*?)}/', $action['desc'], $match)) {    //如果描述中有占位符，根据占位符获取返回结果中的内容填充到占位符中
                            foreach ($match[1] as $k => $v) {
                                $field_arr = explode('.', $v);
                                $value = $res['result'];
                                foreach ($field_arr as $field) {
                                    if (isset($value[$field])) {
                                        $value = $value[$field];
                                    } else {
                                        $value = '';
                                        break;
                                    }
                                }
                                $action['desc'] = str_replace($match[0][$k], $value, $action['desc']);
                            }
                        }
                        $op .= ' ' . $action['desc'];
                    }
                } else {
                    $status = 0;
                }
            } else {
                $status = 0;
            }

        }

        // 插入操作日志
        $data = ['op' => $op, 'status' => $status, 'name' => $name];
        $this->log::query()->create($data);
    }
}
