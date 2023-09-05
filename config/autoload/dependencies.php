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


return [
    \App\Service\Interfaces\UserServiceInterface::class => \App\Service\UserService::class,
    \App\Service\Interfaces\TokenServiceInterface::class => \App\Service\TokenService::class,
    \App\Service\Interfaces\WorkWechatServiceInterface::class => \App\Service\WorkWechatService::class,
    \App\Service\Interfaces\DepartmentServiceInterface::class => \App\Service\DepartmentService::class,
    \App\Service\Interfaces\RolesServiceInterface::class => \App\Service\RolesService::class,
    \App\Service\Interfaces\PermissionsServiceInterface::class => \App\Service\PermissionsService::class,
    \App\Service\Interfaces\TestServiceInterface::class => \App\Service\TestService::class,
    \App\Service\Interfaces\LogServiceInterface::class => \App\Service\LogService::class,
];
