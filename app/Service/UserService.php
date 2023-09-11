<?php


namespace App\Service;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Helper\CommonHelper;
use App\Model\Department;
use App\Model\RolePermissions;
use App\Model\Roles;
use App\Model\UserRole;
use App\Model\Users;
use App\Model\WechatUser;
use App\Service\Interfaces\PermissionsServiceInterface;
use App\Service\Interfaces\TokenServiceInterface;
use App\Service\Interfaces\UserServiceInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use JetBrains\PhpStorm\ArrayShape;
use Phper666\JWTAuth\JWT;
use function Hyperf\Support\make;
use function Hyperf\Support\env;

class UserService extends AbstractService implements UserServiceInterface
{

    #[Inject]
    protected Users $model;

    #[Inject]
    protected Department $department;

    #[Inject]
    protected Roles $role;

    #[Inject]
    protected JWT $jwt;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected WechatUser $wechatUser;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getUserById(int $id, array $columns = ['*']): array
    {
        return $this->model::query()->find($id, $columns)?->toArray();
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果
     */
    public function getUserList(array $where, array $columns = ['*'], array $options = []): array
    {
        array_push($columns, "department.name AS department", "roles.name AS role", "user_role.role_id", "wechat_user.wc_uid", "wechat_user.wc_username");
        $data = $this->model::query()
            ->leftJoin('department', 'department.id', '=', 'users.department_id')
            ->leftJoin('user_role', 'user_role.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'user_role.role_id')
            ->leftJoin('wechat_user', 'wechat_user.uid', '=', 'users.id')
            ->where($where)
            ->select($columns)->selectRaw('ifnull(wechat_user.status, 0) as wechat_user_status')
            ->paginate($options['prePage'] ? intval($options['prePage']) : 15, ['*'], 'page', $options['page'] ? intval($options['page']) : 1)->toArray();
        if (empty($data)) {
            return [];
        }
        foreach ($data['data'] as $k => $v) {
            $exa = explode('-', $v['last_login'] ?: '');
            $data['data'][$k]['last_login'] = !empty($exa[0]) ? date('Y-m-d H:i', $exa[0]) : '';
        }
        return $data;
    }

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     */
    public function getUserInfo(int $id): array
    {
        $user = $this->getUserById($id);
        if (empty($user) || $user['status'] == 0) {
            return [];
        }
        // 屏蔽密码
        unset($user['password']);

        // 封装部门信息
        $departmentInfo = $this->department::query()->find($user['department_id'])?->toArray();
        if (!empty($departmentInfo)) {
            $user['department'] = $departmentInfo['name'];
        } else {
            $user['department'] = '';
        }

        // 封装角色信息
        $userRole = UserRole::query()->where('user_id', $user['id'])->first();
        if (!empty($userRole)) {
            $user['role'] = $userRole->role_id;
        } else {
            $user['role'] = 0;
        }

        // 封装权限信息
        if (!empty($userRole->role_id)) {
            $user['permissions'] = RolePermissions::query()
                ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                ->where('role_permission.role_id', $userRole->role_id)
                ->pluck('permissions.tag')
                ->toArray();
        } else {
            $user['permissions'] = [];
        }
        return $user;
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createUser(array $data): int
    {
        // 加密
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if (empty($data['role_id'])) {
            $res = $this->model::query()->create($data);
        } else {
            // 新增用户、以及用户和角色的对应关系
            $res = Db::transaction(function () use ($data) {
                $roleId = $data['role_id'];
                $wc_uid = $data['wc_uid'];
                unset($data['role_id'], $data['wc_uid']);
                $re = Users::query()->create($data);
                $userId = $re->id;
                $userRole = ['role_id' => $roleId, 'user_id' => $userId];
                UserRole::query()->insert($userRole);

                $data['id'] = $userId;
                $data['wc_uid'] = $wc_uid;
                $this->autoMapWechatUsers($data);
                return $userId;
            });
        }

        return $res;
    }

    /**
     * 自动绑定微信用户
     */
    private function autoMapWechatUsers($data)
    {
        if ($data['name'] && $data['wc_uid']) {
            try {
                $this->mapWechatUser(['uid' => $data['id'], 'wc_uid' => $data['wc_uid'], 'wc_username' => $data['name']]);
            } catch (\Exception $e) {

            }
        } else {
            try {
                $this->unMapWechatUser($data['id']);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateUserById(int $id, array $data): int
    {
        // 判断是否更新了密码
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (empty($data['role_id'])) {
            $res = $this->model::query()->where('id', $id)->update($data);
        } else {
            // 更新用户、以及用户和角色的对应关系
            $res = Db::transaction(function () use ($id, $data) {
                $roleData = ['role_id' => $data['role_id']];
                $wc_uid = $data['wc_uid'];
                unset($data['role_id'], $data['wc_uid']);

                // 先更新用户表信息
                if (!empty($data)) {
                    Users::query()->where('id', $id)->update($data);
                }

                // 新增或更新用户角色的映射表
                UserRole::query()->updateOrInsert(['user_id' => $id], $roleData);

                $data['id'] = $id;
                $data['wc_uid'] = $wc_uid;
                $this->autoMapWechatUsers($data);
                return $id;
            });
        }
        return $res;
    }

    /**
     * 只是修改用户状态
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteUser(int $id): int
    {
        $this->unMapWechatUser($id);
        return $this->model::query()->where('id', $id)->update(['status' => 0]);
    }

    public function enableUser(int $id): int
    {
        $user = $this->model::query()->find($id)?->toArray();
        $res = $this->model::query()->where('id', $id)->update(['status' => 1]);
        $this->mapWechatUser(['uid' => $user['id'], 'wc_uid' => $user['username'], 'wc_username' => $user['name'], 'status' => 1]);
        return $res;
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     */
    #[ArrayShape(['id' => "mixed", 'username' => "mixed", 'name' => "mixed", 'tts' => "\int|mixed", 'token' => "\Lcobucci\JWT\Token|string"])]
    public function login(array $data): array
    {
        // 可以用户名或者邮箱来进行登录
        if (!isset($data['username']) && !isset($data['email'])) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS, '请输入用户名或者邮箱');
        }
        $userInfo = [];
        if (isset($data['username'])) {
            $userInfo = $this->model::query()->where('username', $data['username'])->first()?->toArray();
        }
        if (empty($userInfo) && isset($data['email'])) {
            $userInfo = $this->model::query()->where('email', $data['email'])->first()?->toArray();
        }
        if (empty($userInfo)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }

        // 检验用户密码
        if (!password_verify($data['password'], $userInfo['password'])) {
            throw new BusinessException(ErrorCode::AUTH_LOGIN_FAILED);
        }

        return $this->_loginOpt($userInfo);
    }

    #[ArrayShape(['id' => "mixed", 'username' => "mixed", 'name' => "mixed", 'tts' => "int|mixed", 'token' => "\Lcobucci\JWT\Token|string"])]
    private function _loginOpt($userInfo): array
    {
        if (empty($userInfo)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }
        // 判断用户状态
        if (!$userInfo['status']) {
            throw new BusinessException(ErrorCode::USER_FORBID);
        }
        // 获取用户的角色和权限，将结果强制转换为数组。应该在应该就一开始就设置 Listener 将返回结果设置为数组的
        $roleInfo = $this->model->getRoleInfoByUserId($userInfo['id']);
        if (empty($roleInfo)) {
            throw new BusinessException(ErrorCode::USER_ROLE_EMPTY);
        }
        // 获取角色的权限信息
        $permissionInfo = $this->role->getPermissionInfoByRoleId($roleInfo['id'], ['permissions.id']);
        if (empty($permissionInfo)) {
            throw new BusinessException(ErrorCode::ROLE_PERMISSION_EMPTY);
        }
        $permissionInfo = array_column($permissionInfo, 'id');
        // 获取部门名称
        $department = $this->department::query()->find($userInfo['department_id'])?->toArray();

        // 生成 token
        $userRes = ['id' => $userInfo['id'], 'username' => $userInfo['username'], 'name' => $userInfo['name']];
        $tokenInfo = $userRes;
        $tokenInfo['roleInfo'] = ['id' => $roleInfo['id'], 'position' => $userInfo['position'], 'department_id' => $userInfo['department_id']];
        $tokenInfo['permissionInfo'] = $permissionInfo;
        $tokenInfo['department'] = $department['name'] ?? '';
        $tokenInfo['department_id'] = $userInfo['department_id'];
        $tokenInfo['ip'] = make(CommonHelper::class)->ip();
        $token = $this->jwt->setScene('default')->getToken('default', $tokenInfo);
        $userRes['token'] = $token->toString();
        $userRes['tts'] = $this->jwt->getTTL($userRes['token']);
        // 更新登录时间
        $exa = explode('-', $userInfo['last_login'] ?: '');
        Users::query()->where('id', $userInfo['id'])->update(['last_login' => ($exa[1] ?? '') . '-' . time()]);
        return $userRes;
    }

    /**
     * 刷新用户 token
     */
    public function refreshToken(string $token): array
    {
        $newToken = $this->jwt->refreshToken($token);
        $userRes['token'] = $newToken->toString();
        $userRes['tts'] = $this->jwt->getTTL($userRes['token']);
        return $userRes;
    }

    /**
     * 登出
     */
    public function logout(): bool
    {
        return $this->jwt->logout();
    }

    /**
     * 新增微信用户映射
     * @param array $data 用户信息
     */
    public function mapWechatUser(array $data)
    {
        $user = $this->model->find($data['uid'])?->toArray();
        if (empty($user)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }

        $hadInfo = $this->wechatUser::query()->where('wc_uid', '=', $data['wc_uid'])->select('id')->first()?->toArray();
        if ($hadInfo) {
            throw new BusinessException(ErrorCode::USER_HAD_CONTACT);
        }

        $this->wechatUser::query()->where('uid', $data['uid'])->delete();
        $this->wechatUser::query()->create($data);
    }

    /**
     * 解除微信用户映射
     * @param int $id 用户ID
     */
    public function unMapWechatUser(int $id)
    {
        $user = $this->model->find($id)?->toArray();
        if (empty($user)) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }
        $this->wechatUser::query()->where('uid', $id)->delete();
    }

    /**
     * 获取用户树。以部门的形式来展开
     */
    public function usersTree(): array
    {
        $ret = $this->model->getUsersTree();
        $data = array();
        if (!empty($ret)) {
            foreach ($ret as $item) {
                $data[$item['department_name']][] = ['id' => $item['id'], 'name' => $item['name']];
            }
        }
        return $data;
    }

    /**
     * 单点登录
     * @param array $data 用户信息
     * @return array 登录信息
     */
    public function singleLogin(array $data): array
    {
        $uri = env('QBSC_BASE_URL') . '/dsclient/index.html/login_error?code=';
        $key = 'deepseas_dashboard';
        $sign = md5(substr(md5($data['app_id'] . $data['wxuid'] . $data['code'] . $data['time'] . $key), 6, 20));
        if ($sign != $data['sign']) {
            return ['url' => $uri . '2'];
        }

        $url = env('QBSC_BASE_URL') . '/api/third_party/check_login';
        $rec = make(CommonHelper::class)->makeRequest('post', $url, $data);
        $rec = json_decode($rec['result'], true);
        if ($rec['code'] != 0) {
            return ['url' => $uri . '2'];
        }

        $hadInfo = $this->wechatUser::query()->where(['wc_uid' => $data['wxuid'], 'status' => 1])->select('uid')->first()?->toArray();
        if (!$hadInfo) {
            return ['url' => $uri . '1'];
        }

        $userInfo = $this->model->getUserById($hadInfo['uid']);
        return $this->_loginOpt($userInfo);
    }


    public function getRoutes(): array
    {
        $role = $this->getContainer()->get(TokenServiceInterface::class)->getRoleInfo();
        $permission_ids = $this->getContainer()->get(RolePermissions::class)->getRolePermissions($role['id']);
        return $this->getContainer()->get(PermissionsServiceInterface::class)->getPermissionList(
            [['id', 'IN', $permission_ids], ['path', '!=', '']],
            options: ['orderBy' => 'sort', 'get_routes' => true]
        );
    }
}
