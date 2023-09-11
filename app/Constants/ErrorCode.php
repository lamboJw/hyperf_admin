<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("未知错误")
     * @HttpCode("400")
     */
    const UNKNOWN_ERROR = 100000;
    /**
     * @Message("Token 失效")
     * @HttpCode("401")
     */
    const TOKEN_INVALID = 100001;

    /**
     * @Message("用户或密码错误")
     * @HttpCode("401")
     */
    const AUTH_LOGIN_FAILED = 100002;

    /**
     * @Message("非法登录信息")
     * @HttpCode("401")
     */
    const AUTH_TOKEN_INVALID = 100003;

    /**
     * @Message("登录信息已过期，请重新登录")
     * @HttpCode("401")
     */
    const AUTH_SESSION_EXPIRED = 100004;

    /**
     * @Message("没有登录信息，请重新登录")
     * @HttpCode("401")
     */
    const AUTH_UNAUTHORIZED = 100005;

    /**
     * @Message("登录信息认证失败")
     * @HttpCode("401")
     */
    const AUTH_FAILED = 100006;

    /**
     * @Message("没有权限")
     * @HttpCode("403")
     */
    const ACCESS_DENIED = 100007;

    /**
     * @Message("非法的参数")
     * @HttpCode("422")
     */
    const INVALID_PARAMS = 100008;


    /**
     * @Message("禁止用户登录")
     * @HttpCode("422")
     */
    const USER_FORBID = 100009;

    /**
     * @Message("用户不存在")
     * @HttpCode("422")
     */
    const USER_NOT_EXIST = 100010;

    /**
     * @Message("该账号已被关联，请先解绑后重新关联")
     * @HttpCode("422")
     */
    const USER_HAD_CONTACT = 100011;

    /**
     * @Message("请联系管理员创建申请人账号")
     * @HttpCode("422")
     */
    const USER_HAD_NOT_CONTACT = 100012;

    /**
     * @Message("该账号尚未关联且企业微信用户")
     * @HttpCode("422")
     */
    const WECHAT_USER_NOT_MAP = 100013;

    /**
     * @Message("code 不能为空")
     * @HttpCode("422")
     */
    const CODE_SHOULD_NOT_BE_NULL = 100014;


    /**
     * @Message("微信登录失败")
     * @HttpCode("422")
     */
    const WECHAT_LOGIN_FAILED = 100015;

    /**
     * @Message("该用户不是企业微信用户")
     * @HttpCode("422")
     */
    const WECHAT_USER_ERROR = 100016;

    /**
     * @Message("添加失败")
     * @HttpCode("422")
     */
    const ADD_FAIL = 100017;

    /**
     * @Message("删除失败")
     * @HttpCode("422")
     */
    const DELETE_FAIL = 100018;

    /**
     * @Message("更新失败")
     * @HttpCode("422")
     */
    const UPDATE_FAIL = 100019;

    /**
     * @Message("启用失败")
     * @HttpCode("422")
     */
    const ENABLE_FAIL = 100020;

    /**
     * @Message("登出失败")
     * @HttpCode("422")
     */
    const LOGOUT_FAIL = 100021;

    /**
     * @Message("不是doc或docx文件，不能转换")
     * @HttpCode("422")
     */
    const CONV_PDF_EXT_INVALID = 100022;

    /**
     * @Message("文件不存在")
     * @HttpCode("422")
     */
    const CONV_PDF_FILE_NOT_EXISTS = 100023;

    /**
     * @Message("转换PDF格式失败")
     * @HttpCode("422")
     */
    const CONV_PDF_FAIL = 100024;

    /**
     * @Message("当前角色没有任何权限")
     * @HttpCode("422")
     */
    const ROLE_PERMISSION_EMPTY = 100025;
    /**
     * @Message("当前用户没有授权任何角色")
     * @HttpCode("422")
     */
    const USER_ROLE_EMPTY = 100026;

    /**
     * @Message("您没有该游戏的权限")
     * @HttpCode("422")
     */
    const PARENT_GAME_NOT_AUTH = 100027;
}
