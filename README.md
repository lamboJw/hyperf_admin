# Hyperf后台后端模板
配合 [vue-admin-layout]() 前端模板使用

## 环境需求
+ php 8.1
+ MySql 8.0
+ Redis
+ composer 2.4.4

## 配置修改
1. 修改`app/Constants/WorkWechat.php`，填写自己的企业微信配置，用于获取企业的部门、员工信息
2. 修改`.env`文件中的mysql、redis配置

## 安装
1. 项目根目录，执行`composer install`
2. 在 MySql 中运行项目的`doc/database.sql`文件初始化数据库
3. nginx中添加配置文件，`doc/hyperf_admin`
