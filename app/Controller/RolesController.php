<?php


namespace App\Controller;


use App\Constants\ErrorCode;
use App\Constants\OrderMain;
use App\Exception\BusinessException;
use App\Request\PaginateRequest;
use App\Request\Roles\RolesAddRequest;
use App\Request\Roles\RolesUpdateRequest;
use App\Service\Interfaces\RolesServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class RolesController extends AbstractController
{

    #[Inject]
    protected RolesServiceInterface $roles;


    public function add(RolesAddRequest $rolesAddRequest): array
    {
        // 参数校验
        $rolesAddRequest->validated();
        // 请求参数
        $params = $this->request->all();
        $res = $this->roles->createRole($params);
        if ($res == -1) {
            throw new BusinessException(ErrorCode::ADD_FAIL);
        }
        return $this->success();
    }


    public function update(RolesUpdateRequest $rolesUpdateRequest): array
    {
        // 参数校验
        $rolesUpdateRequest->validated();
        // 请求参数
        $params = $this->request->all();
        $id = $params['id'];
        unset($params['id']);
        $res = $this->roles->updateRoleById($id, $params);
        if ($res == -1) {
            throw new BusinessException(ErrorCode::UPDATE_FAIL);
        }
        return $this->success();
    }

    public function list(PaginateRequest $paginateRequest): array
    {
        $paginateRequest->validated();
        // 请求参数
        $params = $this->request->all();
        $where = [];
        $name = $this->request->input('name');
        if ($name) {
            // like 的值在前后要加上 %
            $where[] = ['name', 'LIKE', '%' . $name . '%'];
        }
        $res = $this->roles->getRoleList($where, ['*'], $params);
        return $this->success($res);
    }

    public function all(): array
    {
        $res = $this->roles->getAllRole();
        return $this->success($res);
    }


    public function info(RolesUpdateRequest $rolesUpdateRequest): array
    {
        // 校验参数
        $validated = $rolesUpdateRequest->validated();
        $id = $this->request->input("id");
        $res = $this->roles->getRoleById($id, ['id', 'name', 'desc']);
        return $this->success($res);
    }

    public function delete(RolesUpdateRequest $rolesUpdateRequest): array
    {
        // 校验参数
        $validated = $rolesUpdateRequest->validated();
        $id = $this->request->input("id");
        $res = $this->roles->deleteRole($id);
        if (!$res) {
            throw new BusinessException(ErrorCode::DELETE_FAIL);
        }
        return $this->success();
    }


}
