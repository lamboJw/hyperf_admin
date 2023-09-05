<?php

declare(strict_types=1);

namespace App\Service\Interfaces;

interface UserServiceInterface
{

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     */
    public function getUserById(int $id, array $columns = ['*']): array;


    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     */
    public function getUserInfo(int $id): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getUserList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createUser(array $data): int;


    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function login(array $data): array;

    /**
     * 刷新用户 token
     */
    public function refreshToken(string $token): array;


    /**
     * 登出
     */
    public function logout(): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateUserById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteUser(int $id): int;

    /**
     * 启用 - 单条
     * @param int $id 操作ID
     * @return int 操作条数
     */
    public function enableUser(int $id): int;

    /**
     * 新增微信用户映射
     * @param array $data 用户信息
     */
    public function mapWechatUser(array $data);

    /**
     * 解除微信用户映射
     * @param int $id 用户ID
     */
    public function unMapWechatUser(int $id);

    /**
     * 获取用户树。以部门的形式来展开
     */
    public function usersTree(): array;

    /**
     * 获取用户可用路由
     * @return array
     */
    public function getRoutes(): array;
}
