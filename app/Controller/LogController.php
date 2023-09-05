<?php


namespace App\Controller;

use App\Request\PaginateRequest;
use App\Service\Interfaces\LogServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class LogController extends AbstractController {

    #[Inject]
    protected LogServiceInterface $log;

    public function list(PaginateRequest $paginateRequest): array
    {
        $paginateRequest->validated();
        $params = $this->request->all();
        $res = $this->log->getLogList($params, ['*'], $params);
        return parent::success($res);
    }

}
