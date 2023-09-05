<?php
/* 企业微信接口 */
declare(strict_types=1);

namespace App\Controller;

use App\Service\Interfaces\WorkWechatServiceInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;

#[AutoController]
class WorkWechatController extends AbstractController
{

    #[Inject]
    protected WorkWechatServiceInterface $workWechat;

    /* 获取部门 */
    public function department(): array
    {
        return $this->success($this->workWechat->department());
    }

    /* 获取部门成员 */
    public function user(): array
    {
        $department_id = intval($this->request->input('department_id'));
        return $this->success($this->workWechat->user($department_id));
    }

    public function login()
    {
        $code = $this->request->input('code');
        $res = $this->workWechat->login($code);
        return $this->success($res);
    }

    public function getMenu()
    {
        return $this->workWechat->get_menu();
    }

    public function setMenu()
    {
        return $this->workWechat->set_menu();
    }

    public function departmentUserTree(): array
    {
        $need_root = $this->request->input('need_root');
        $res = $this->workWechat->department_user_tree($need_root);
        return $this->success($res);
    }
}
