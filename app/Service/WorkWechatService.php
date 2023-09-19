<?php


namespace App\Service;

use App\Constants\ErrorCode;
use App\Model\Department;
use App\Model\Users;
use App\Model\WechatUser;
use App\Service\Interfaces\WorkWechatServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Logger\LoggerFactory;
use Phper666\JWTAuth\JWT;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Exception\BusinessException;
use App\Constants\WorkWechat;
use Hyperf\DbConnection\Traits\HasContainer;
use function Hyperf\Support\env;

class WorkWechatService implements WorkWechatServiceInterface
{

    use HasContainer;

    #[Inject]
    protected EventDispatcherInterface $dispatcher;


    #[Inject]
    protected WechatUser $wechatUser;

    #[Inject]
    protected Users $user;

    #[Inject]
    protected Department $department;

    #[Inject]
    protected TokenService $tokenService;

    public $token;

    #[Inject]
    private JWT $jwt;

    /**
     * 获取部门
     */
    #[Cacheable(prefix: "workWechatDepartment", ttl: 86400, listener: "workWechatDepartment")]
    public function department(): array
    {
        $this->getToken();
        $url = WorkWechat::BASE_URL . '/cgi-bin/department/list?access_token=' . $this->token;
        $res = json_decode(file_get_contents($url), true);
        if ($res['errcode'] == 0) {
            $data = array_map(function ($v) {
                return ['id' => $v['id'], 'name' => $v['name']];
            }, $res['department']);
            $this->department->truncated();
            $this->department::query()->insert($data);
            return $res['department'];
        } else {
            throw new BusinessException(3, '获取部门失败：' . $res['errmsg']);
        }
    }


    /**
     * 获取部门成员
     */
    #[Cacheable(prefix: "workWechatUser", ttl: 86400, listener: "workWechatUser")]
    public function user(int $department_id): array
    {
        $this->getToken();
        $url = WorkWechat::BASE_URL . '/cgi-bin/user/simplelist?access_token=' . $this->token . "&department_id=$department_id&fetch_child=1";
        $res = json_decode(file_get_contents($url), true);
//        print_r($res);
        if ($res['errcode'] == 0) {
            return $res['userlist'];
        } else {
            throw new BusinessException(4, '获取部门成员失败：' . $res['errmsg']);
        }
    }

    /**
     * 获取部门成员完整信息
     * @param int $department_id
     * @return array
     */
    public function userFully(int $department_id): array
    {
        $this->getToken();
        $url = WorkWechat::BASE_URL . '/cgi-bin/user/list?access_token=' . $this->token . "&department_id=$department_id";
        $res = json_decode(file_get_contents($url), true);
//        print_r($res);
        if ($res['errcode'] == 0) {
            return $res['userlist'];
        } else {
            throw new BusinessException(4, '获取部门成员失败：' . $res['errmsg']);
        }
    }

    /**
     * 推送消息
     */
    public function sendMsg(array $data): array
    {
        if (env('APP_ENV') == 'local') return ['本地环境不发送消息'];
        if (isset($data['content']) && !empty($data['content'])) {
            // 获取微信用户信息
            $wechatUserInfo = $this->wechatUser->getOneByCondition([['uid', '=', $data['user_id']]], ['wc_uid']);
            // 如果是没有绑定，那就不发送。直接返回，不要影响合同的执行和审批
            if (!$wechatUserInfo) {
                return [];
            }
            $sendto = $wechatUserInfo['wc_uid'];
            $merge_data = [
                'msgtype' => 'text',
                'text' => ['content' => $data['content']]
            ];
        } elseif (!empty($data['file'])) {
            $wechatUserInfo = $this->wechatUser->getOneByCondition([['uid', '=', $data['user_id']]], ['wc_uid']);
            if (!$wechatUserInfo) {
                return [];
            }
            $sendto = $wechatUserInfo['wc_uid'];
            $merge_data = [
                'msgtype' => 'file',
                'file' => [
                    'media_id' => $data['file']
                ]
            ];
        } else {
            if (isset($data['title'])) { //自定义模板消息
                $merge_data = [
                    "msgtype" => "textcard",
                    "textcard" => [
                        "title" => $data['title'],
                        "description" => $data['desc'],
                        'url' => $data['url'],
                        "btntxt" => '查看详情',
                    ],
                ];
                $wechatUserInfo = $this->wechatUser->getOneByCondition([['uid', '=', $data['user_id']]], ['wc_uid']);
                $sendto = $wechatUserInfo['wc_uid'];
            }
        }

        $dataPush = [
            "touser" => $sendto,
            "agentid" => WorkWechat::AGENTID,
        ];
        $dataPush = array_merge($dataPush, $merge_data);

        $this->getToken();
        $url = WorkWechat::BASE_URL . '/cgi-bin/message/send?access_token=' . $this->token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataPush));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        $rec = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($rec, true);
        if ($res['errcode'] == 0) {
            return ['推送成功'];
        } else {
            throw new BusinessException(4, '消息推送失败：' . $res['errmsg']);
        }
    }


    /**
     * 获取token、
     */
    public function getToken()
    {
        $this->token = $this->_getToken();
        if ($this->token == 'err' || !$this->token || $this->token == '') {
            $this->flushCache();

            $this->token = $this->_getTokenNoCache();
            if ($this->token == 'err' || !$this->token || $this->token == '') {
                throw new BusinessException(2, '获取 token 失败');
            }
        }
    }
    /**
     * 获取token、带缓存
     * @Cacheable(prefix="workWechatToken", ttl=7000, listener="workWechatToken")
     */
    // 微信规定 token 7200 秒后失效
    public function _getToken()
    {
        return $this->_getTokenNoCache();
    }

    /**
     * 获取token、无缓存
     */
    public function _getTokenNoCache()
    {
        $url = WorkWechat::BASE_URL . '/cgi-bin/gettoken?corpid=' . WorkWechat::CORPID . '&corpsecret=' . WorkWechat::SECRET;
        $res = json_decode(file_get_contents($url), true);
        if ($res['errcode'] == 0) {
            return $res['access_token'];
        } else {
            return 'err';
        }
    }

    /**
     * 删除token缓存
     */
    public function flushCache(): bool
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('workWechatToken', []));
        return true;
    }

    /**
     * 登录
     */
    public function login(string $code): array
    {
        if (empty($code)) {
            throw new BusinessException(ErrorCode::CODE_SHOULD_NOT_BE_NULL);
        }
        $this->getToken();
        $uri = 'cgi-bin/user/getuserinfo?access_token=' . $this->token . '&code=' . $code;

        /*$client = new Client([
            'base_uri' => WorkWechat::BASE_URL,//本地测试地址
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],
        ]);
        $response = $client->get($uri);
        if($response->getStatusCode() !== 200){
            throw new BusinessException(ErrorCode::HTTP_REQUEST_ERROR);
        }
        $resJsonStr =  $response->getBody()->getContents();*/
        $resJsonStr = file_get_contents(WorkWechat::BASE_URL . '/' . $uri);
        $resArr = json_decode($resJsonStr, true);
        if ($resArr['errcode'] !== 0) {
            \Hyperf\Utils\ApplicationContext::getContainer()->get(LoggerFactory::class)->get('logger')->info('微信登录失败', $resArr);
            throw new BusinessException(ErrorCode::WECHAT_LOGIN_FAILED);
        }
        if (!isset($resArr['UserId'])) {
            throw new BusinessException(ErrorCode::WECHAT_USER_ERROR);
        }

        // 获取微信用户信息
        $wechatUserInfo = $this->wechatUser->getOneByCondition([['wc_uid', '=', $resArr['UserId']]], ['uid']);
        if (empty($wechatUserInfo)) {
            throw new BusinessException(ErrorCode::WECHAT_USER_NOT_MAP);
        }

        // 获取用户信息
        $userInfo = $this->user->getUserById($wechatUserInfo['uid']);
        if (empty($userInfo)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }

        // 获取用户的角色和权限，将结果强制转换为数组。应该在应该就一开始就设置 Listener 将返回结果设置为数组的
        $roleInfo = (array)Db::table("roles")
            ->join("user_role", "roles.id", "=", "user_role.role_id")
            ->where("user_role.user_id", $userInfo['id'])
            ->select('roles.*')
            ->get()->toArray()[0];
        // 获取角色的权限信息
        $permissionInfo = Db::table("permissions")
            ->join("role_permission", "permissions.id", "=", "role_permission.permission_id")
            ->where("role_permission.role_id", $roleInfo['id'])
            ->select('permissions.id')
            ->get()->toArray();
        // 改为权限 ID 数组
        $permissionInfo = array_map(function ($permissionObj) {
            return $permissionObj->id;
        }, $permissionInfo);

        // 获取部门名称
        $department = $this->department->getOneById($userInfo['department_id']);


        // 生成 token
        $userRes = ['id' => $userInfo['id'], 'username' => $userInfo['username'], 'name' => $userInfo['name'], 'email' => $userInfo['email']];
        $tokenInfo = $userRes;
        $tokenInfo['roleInfo'] = $roleInfo;
        $tokenInfo['permissionInfo'] = $permissionInfo;
        $tokenInfo['department'] = $department['name'];
        $tokenInfo['department_id'] = $userInfo['department_id'];
        $token = $this->jwt->setScene('default')->getToken('default', $tokenInfo);
        $userRes['token'] = $token->toString();
        $userRes['tts'] = $this->jwt->getTTL($userRes['token']);

        return $userRes;

    }

    public function get_menu()
    {
        $this->getToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token={$this->token}&agentid=" . WorkWechat::AGENTID;
        $re = makeRequest('GET', $url);
        return json_decode($re['result'], true);
    }

    public function set_menu()
    {
        $this->getToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token={$this->token}&agentid=" . WorkWechat::AGENTID;
        $contract_url = env('HTML_BASE_URL') . '/#/contract-h5';
        $todo_url = env('HTML_BASE_URL') . '/#/order_todo?active=todo';
        $data = [
            'button' => [
                [
                    'type' => 'view',
                    'name' => '待审批',
                    'url' => $this->auth_url($contract_url, 1),
                ],
                [
                    'type' => 'view',
                    'name' => '已办理',
                    'url' => $this->auth_url($contract_url, 2),
                ],
                [
                    'type' => 'view',
                    'name' => '待处理任务',
                    'url' => $this->auth_url($todo_url),
                ],
            ]
        ];
        $re = makeRequest('POST', $url, $data, 'application/json');
        return json_decode($re['result'], true);
    }

    protected function auth_url($redirect_uri, $state = ''): string
    {
        $redirect_uri = urlencode($redirect_uri);
        $appid = WorkWechat::CORPID;
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }

    public function uploadMedia($filepath, $filename, $type)
    {
        if (!file_exists($filepath)) {
            $return = ['errcode' => -1, 'errmsg' => '文件不存在'];
            $this->getContainer()->get(LoggerFactory::class)->get('logger')->error('上次临时素材失败', array_merge(compact('filepath', 'type'), $return));
            return $return;
        }
        $this->getToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token={$this->token}&type={$type}";
        $data['media'] = new \CURLFile($filepath, null, $filename);
        $result = makeRequest('POST', $url, $data, 'multipart/form-data');
        if ($result['code'] != 200) {
            $return = ['errcode' => -2, 'errmsg' => '上传接口请求失败'];
            $this->getContainer()->get(LoggerFactory::class)->get('logger')->error('上次临时素材失败', array_merge(compact('filepath', 'type'), $return));
            return $return;
        }
        $return = json_decode($result['result'], true);
        if ($return['errcode'] != 0) {
            $this->getContainer()->get(LoggerFactory::class)->get('logger')->error('上次临时素材失败', array_merge(compact('filepath', 'type'), $return));
        }
        return $return;
    }

    /**
     * 获取部门成员
     */
    #[Cacheable(prefix: "DepartmentUserTree", ttl: 86400, listener: "DepartmentUserTree")]
    public function department_user_tree($need_root): array
    {
        $tree = [];
        $department_list = $this->department();
        foreach ($department_list as $department) {
            if (!$need_root && $department['id'] == 1) continue;
            $level1 = [
                'value' => $department['id'],
                'label' => $department['name'],
                'children' => [],
            ];
            $user_list = $this->user($department['id']);
            foreach ($user_list as $user) {
                $level1['children'][] = [
                    'value' => $user['userid'],
                    'label' => $user['name'],
                ];
            }
            $tree[] = $level1;
        }
        return $tree;
    }
}
