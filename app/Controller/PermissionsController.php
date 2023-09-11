<?php


namespace App\Controller;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Request\Permissions\PermissionsAddRequest;
use App\Request\Permissions\PermissionsUpdateRequest;
use App\Service\Interfaces\PermissionsServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class PermissionsController extends AbstractController
{

    #[Inject]
    protected PermissionsServiceInterface $permissions;


    public function add(PermissionsAddRequest $permissionsAddRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $res = $this->permissions->createPermission($params);
        if (!$res) {
            throw new BusinessException(ErrorCode::ADD_FAIL);
        }
        return $this->success();
    }


    public function list(): array
    {
        $res = $this->permissions->getPermissionList();
        return $this->success($res);
    }


    public function delete(): array
    {
        // 参数校验
        $id = $this->request->input('id');
        $this->permissions->deletePermission($id);
        return $this->success();
    }


    public function update(PermissionsUpdateRequest $permissionsUpdateRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $id = $params['id'];
        unset($params['id']);
        $res = $this->permissions->updatePermissionById($id, $params);
        if (!$res) {
            throw new BusinessException(ErrorCode::UPDATE_FAIL);
        }
        return $this->success();
    }

    public function info():array
    {
        $id = $this->request->input('id');
        return $this->success($this->permissions->getPermissionById($id));
    }
}
