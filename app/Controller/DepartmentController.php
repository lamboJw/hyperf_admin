<?php


namespace App\Controller;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Request\Department\DepartmentAddRequest;
use App\Request\Department\DepartmentRequest;
use App\Request\Department\DepartmentUpdateRequest;
use App\Request\PaginateRequest;
use App\Service\Interfaces\DepartmentServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class DepartmentController extends AbstractController
{


    #[Inject]
    protected DepartmentServiceInterface $department;

    public function add(DepartmentAddRequest $departmentAddRequest): array
    {
        // 请求参数
        $name = $this->request->input("name");
        $res = $this->department->createDepartment(["name" => $name]);
        if (!$res) {
            throw new BusinessException(ErrorCode::ADD_FAIL);
        }
        return $this->success();
    }

    public function update(DepartmentUpdateRequest $departmentUpdateRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $id = $params['id'];
        unset($params['id']);
        $res = $this->department->updateDepartmentById($id, $params);
        if (!$res) {
            throw new BusinessException(ErrorCode::UPDATE_FAIL);
        }
        return $this->success();
    }

    public function list(PaginateRequest $paginateRequest): array
    {
        // 请求参数
        $params = $this->request->all();
        $res = $this->department->getDepartmentList([], ['*'], $params);
        return $this->success($res);
    }

    public function delete(DepartmentRequest $departmentRequest): array
    {
        $id = $this->request->input("id");
        $res = $this->department->deleteDepartment($id);
        if (!$res) {
            throw new BusinessException(ErrorCode::DELETE_FAIL);
        }
        return $this->success();
    }
}
