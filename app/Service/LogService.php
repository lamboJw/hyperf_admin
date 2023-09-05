<?php


namespace App\Service;


use App\Model\Log;
use App\Service\Interfaces\LogServiceInterface;
use Hyperf\Di\Annotation\Inject;

class LogService extends AbstractService implements LogServiceInterface {

    #[Inject]
    protected Log $model;
    /**
     * 多条分页.
     * @param array $params 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getLogList(array $params, array $columns = ['*'], array $options = []): array {
        // 日志逆序
        $options['orderByDesc'] = 'created_at';
        $where = [];
        if(!empty($params['op'])) {
            $where[] = ['op', 'like', "%{$params['op']}%"];
        }
        if(!empty($params['name'])) {
            $where[] = ['name', 'like', "%{$params['name']}%"];
        }
        if(!empty($params['created_at'])) {
            $where[] = ['created_at', 'between', $params['created_at']];
        }
        return $this->model->getPageList($where, $columns, $options);
    }
}
