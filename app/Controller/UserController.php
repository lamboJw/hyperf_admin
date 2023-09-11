<?php

namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Request\PaginateRequest;
use App\Request\Users\UserLoginRequest;
use App\Request\Users\UsersAddRequest;
use App\Request\Users\UserTokenRequest;
use App\Request\Users\UserUpdateRequest;
use App\Service\Interfaces\UserServiceInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Phper666\JWTAuth\Util\JWTUtil;

#[AutoController]
class UserController extends AbstractController
{


    #[Inject]
    protected UserServiceInterface $user;


    public function add(UsersAddRequest $usersAddRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $res = $this->user->createUser($params);
        if (!$res) {
            throw new BusinessException(ErrorCode::ADD_FAIL);
        }
        return $this->success();
    }


    public function update(UserUpdateRequest $userUpdateRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $id = $params['id'];
        unset($params['id']);
        $res = $this->user->updateUserById($id, $params);
        if (!$res) {
            throw new BusinessException(ErrorCode::UPDATE_FAIL);
        }
        return $this->success();
    }


    public function delete(UserUpdateRequest $userUpdateRequest): array
    {
        $ids = $this->request->input("ids");
        foreach ($ids as $id) {
            $this->user->deleteUser($id);
        }
        return $this->success();
    }

    public function enableUser(): array
    {
        $ids = $this->request->input('ids');
        foreach ($ids as $id) {
            $this->user->enableUser($id);
        }
        return $this->success();
    }


    public function list(PaginateRequest $paginateRequest): array
    {
        $params = $this->request->all();
        $where = [];
        // 如果有加上用户名则表示模糊搜索
        $name = $this->request->input('name');
        $status = $this->request->input('status');
        if ($name) {
            // like 的值在前后要加上 %
            $where[] = ['users.name', 'LIKE', '%' . $name . '%'];
        }
        if ($status) {
            $where[] = ['users.status', '=', $status];
        }
        $res = $this->user->getUserList($where,
            ['users.id', 'users.username', 'users.name', 'users.email', 'users.department_id', 'users.position', 'users.last_login', 'users.status'],
            $params);
        return $this->success($res);
    }

    public function info(UserUpdateRequest $userUpdateRequest): array
    {
        $id = $this->request->input("id");
        $res = $this->user->getUserInfo($id);
        return $this->success($res);
    }

    public function login(UserLoginRequest $userLoginRequest): array
    {
        $params = $this->request->all();
        $res = $this->user->login($params);

        if (empty($res)) {
            throw new BusinessException(ErrorCode::AUTH_LOGIN_FAILED);
        }
        return $this->success($res);
    }

    public function logout(): array
    {
        // 校验参数
        $token = JWTUtil::getToken($this->request);
        $res = $this->user->logout();
        if (!$res) {
            throw new BusinessException(ErrorCode::LOGOUT_FAIL);
        }
        return $this->success(['token' => $token]);
    }

    public function refresh(UserTokenRequest $userTokenRequest): array
    {
        $token = $this->request->input("token");
        $res = $this->user->refreshToken($token);
        return $this->success($res);
    }


    public function wechat_map(): array
    {
        $params = $this->request->all();
        $res = $this->user->mapWechatUser($params);
        return $this->success($res);
    }

    public function wechat_unmap(): array
    {
        $id = $this->request->input("id");
        $res = $this->user->unMapWechatUser($id);
        return $this->success();
    }


    public function users_tree(): array
    {
        // 校验参数
        $res = $this->user->usersTree();
        return $this->success($res);
    }

    public function single_login(): array
    {
        return $this->success($this->user->singleLogin($this->request->all()));
    }

    public function getRoutes(): array
    {
        return $this->success($this->user->getRoutes());
    }
}
