<?php

declare(strict_types=1);

namespace App\Service\Interfaces;

use Lcobucci\JWT\Token;

interface TokenServiceInterface
{
    public function getToken(): string;

    public function parseToken(): Token;

    public function getUid(): int;

    public function getUsername(): string;

    public function getUidAndUsername(): array;

    public function getRoleInfo(): array;

    public function getDataPermission($type): array;

    public function isSuperAdmin(): bool;

    public function getPermissionInfo(): array;

    public function getPermissionIds(): array;

    public function getDepartmentName(): string;

    public function getDepartmentId(): string;

    public function getEmail();

    public function getJti(): string;
}
