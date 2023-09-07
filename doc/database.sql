/*
 Navicat Premium Data Transfer

 Source Server         : 虚拟机
 Source Server Type    : MySQL
 Source Server Version : 80032
 Source Host           : 127.0.0.1:3306
 Source Schema         : hyperf_admin

 Target Server Type    : MySQL
 Target Server Version : 80032
 File Encoding         : 65001

 Date: 05/09/2023 19:59:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for department
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '部门名称',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '部门表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for op_action
-- ----------------------------
DROP TABLE IF EXISTS `op_action`;
CREATE TABLE `op_action`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '请求 URI',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT 'URI 对应的名字',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '详细描述模板',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '操作行为' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of op_action
-- ----------------------------

-- ----------------------------
-- Table structure for op_log
-- ----------------------------
DROP TABLE IF EXISTS `op_log`;
CREATE TABLE `op_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `op` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态。1表示操作成功，0表示操作失败',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '权限路径',
  `parent_id` int NOT NULL DEFAULT 0 COMMENT '父级权限ID',
  `tag` varchar(35) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '标识',
  `sort` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '小到大排序',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '图标',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `tag`(`tag`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (1, '后台管理', '/admin_manage', 0, 'admin-manage', 8, 'el-icon-setting', '2021-06-16 17:13:43', '2023-09-04 14:21:10');
INSERT INTO `permissions` VALUES (2, '账号管理', '/account', 1, 'account', 1, 'el-icon-user', '2021-06-16 17:44:53', '2023-09-05 11:32:54');
INSERT INTO `permissions` VALUES (3, '角色管理', '/role', 1, 'role', 2, 'el-icon-user-solid', '2021-06-16 17:44:53', '2023-09-05 11:35:42');
INSERT INTO `permissions` VALUES (4, '日志管理', '/log', 1, 'log', 4, 'el-icon-document', '2021-06-16 17:44:53', '2023-09-05 11:32:24');
INSERT INTO `permissions` VALUES (5, '列表', '', 2, 'account-list', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:05');
INSERT INTO `permissions` VALUES (6, '新增', '', 2, 'account-add', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:09');
INSERT INTO `permissions` VALUES (7, '编辑', '', 2, 'account-edit', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:15');
INSERT INTO `permissions` VALUES (8, '删除', '', 2, 'account-del', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:20');
INSERT INTO `permissions` VALUES (9, '列表', '', 3, 'role-list', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:25');
INSERT INTO `permissions` VALUES (10, '新增', '', 3, 'role-add', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:07:47');
INSERT INTO `permissions` VALUES (11, '权限设置', '', 3, 'role-set', 0, '', '2021-06-16 17:44:53', '2023-08-30 16:42:22');
INSERT INTO `permissions` VALUES (12, '列表', '', 4, 'log-list', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:04:28');
INSERT INTO `permissions` VALUES (13, '删除', '', 3, 'role-del', 0, '', '2021-06-16 17:44:53', '2021-06-18 14:07:56');
INSERT INTO `permissions` VALUES (14, '权限列表', '/permission', 1, 'permission', 3, 'el-icon-s-claim', '2023-08-30 14:27:01', '2023-09-05 11:36:04');
INSERT INTO `permissions` VALUES (15, '列表', '', 14, 'permission-list', 1, '', '2023-08-30 14:28:04', '2023-09-05 10:07:56');
INSERT INTO `permissions` VALUES (16, '编辑', '', 14, 'permission-edit', 3, '', '2023-08-30 14:28:16', '2023-09-05 10:08:22');
INSERT INTO `permissions` VALUES (17, '添加', '', 14, 'permission-add', 2, '', '2023-08-30 14:28:36', '2023-09-05 10:08:13');
INSERT INTO `permissions` VALUES (18, '删除', '', 14, 'permission-del', 4, '', '2023-08-30 14:28:46', '2023-09-05 10:08:29');

-- ----------------------------
-- Table structure for role_data_permission
-- ----------------------------
DROP TABLE IF EXISTS `role_data_permission`;
CREATE TABLE `role_data_permission`  (
  `role_id` int NOT NULL DEFAULT 0 COMMENT '角色ID',
  `data_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '数据类型',
  `data_permission` tinyint(1) NOT NULL DEFAULT 1 COMMENT '数据权限',
  `parent_game` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '一级游戏',
  `department` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '指定部门所属人员可见',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `data_type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '角色数据权限' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for role_permission
-- ----------------------------
DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE `role_permission`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `permission_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 94 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '角色权限映射表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of role_permission
-- ----------------------------
INSERT INTO `role_permission` VALUES (65, 1, 1, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (66, 1, 14, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (67, 1, 15, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (68, 1, 17, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (69, 1, 16, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (70, 1, 18, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (71, 1, 4, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (72, 1, 12, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (73, 1, 3, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (74, 1, 11, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (75, 1, 13, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (76, 1, 10, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (77, 1, 9, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (78, 1, 2, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (79, 1, 8, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (80, 1, 7, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (81, 1, 6, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (82, 1, 5, '2023-09-05 11:21:25', NULL);
INSERT INTO `role_permission` VALUES (94, 2, 5, '2023-09-05 15:27:35', NULL);
INSERT INTO `role_permission` VALUES (95, 2, 9, '2023-09-05 15:27:35', NULL);
INSERT INTO `role_permission` VALUES (96, 2, 15, '2023-09-05 15:27:35', NULL);
INSERT INTO `role_permission` VALUES (97, 2, 4, '2023-09-05 15:27:35', NULL);
INSERT INTO `role_permission` VALUES (98, 2, 12, '2023-09-05 15:27:35', NULL);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `created_user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色创建者的用户ID',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '角色描述',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '角色表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, '超级管理员', 1, '1', '2023-08-30 14:19:39', '2023-09-05 11:21:25');
INSERT INTO `roles` VALUES (2, '测试角色', 1, '123123', '2023-09-05 14:21:45', '2023-09-05 15:27:35');

-- ----------------------------
-- Table structure for user_role
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `role_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户角色映射表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_role
-- ----------------------------
INSERT INTO `user_role` VALUES (1, 1, 1, '2023-08-31 15:50:58', NULL);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '用户密码',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `department_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '部门ID',
  `position` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '1普通用户 2部门主管 3总经理',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态。1表示可以，0表示禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_login` varchar(21) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '上次登录',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '超级管理员', '$2y$10$.QoS7J0nctOWu/0utMuBbuo24fC833q3Y/VwxXWSq0RKJA6rWDbwu', 'xxxx@qq.com', 20, 3, 1, '2021-06-18 15:55:43', '2023-09-05 18:22:01', '1693898992-1693909321');

-- ----------------------------
-- Table structure for wechat_user
-- ----------------------------
DROP TABLE IF EXISTS `wechat_user`;
CREATE TABLE `wechat_user`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int NULL DEFAULT 0 COMMENT '系统的用户ID',
  `wc_username` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '微信用户名',
  `wc_uid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '微信用户ID',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态。1表示可以，0表示禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '微信用户' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
