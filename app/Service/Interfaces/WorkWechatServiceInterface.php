<?php


namespace App\Service\Interfaces;

interface WorkWechatServiceInterface
{

    /**
     * 获取部门
     */
    public function department(): array;


    /**
     * 获取部门成员
     */
    public function user(int $department_id): array;

    /**
     * 获取部门成员完整字段
     */
    public function userFully(int $department_id): array;

    /**
     * 登录
     */
    public function login(string $code): array;


    /**
     * 推送消息
     */
    public function sendMsg(array $data): array;

    /**
     * 获取token、带缓存
     */
    public function getToken();

    /**
     * 删除token缓存
     */
    public function flushCache(): bool;


    public function get_menu();

    public function set_menu();

    public function uploadMedia($filepath, $filename, $type);

    public function department_user_tree($need_root): array;
}
