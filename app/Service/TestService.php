<?php

namespace App\Service;

use App\Model\Department;
use App\Service\Interfaces\TestServiceInterface;
use Hyperf\Di\Annotation\Inject;

class TestService extends AbstractService implements TestServiceInterface
{
    #[Inject]
    protected Department $model;

    public function test(){
        return $this->model->get()->toArray();
    }
}
