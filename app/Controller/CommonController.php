<?php

namespace App\Controller;

use App\Model\ParentGame;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class CommonController extends AbstractController
{

    public function get_all_parent_game(): array
    {
        return $this->success($this->container->get(ParentGame::class)->getAll());
    }
}
