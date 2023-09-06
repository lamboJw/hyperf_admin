<?php


namespace App\Service;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Permissions;
use App\Model\RoleDataPermissions;
use App\Model\WechatUser;
use App\Service\Interfaces\RolesServiceInterface;
use App\Service\Interfaces\TokenServiceInterface;
use App\Service\Interfaces\WorkWechatServiceInterface;
use DateTime;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use JetBrains\PhpStorm\ArrayShape;
use Lcobucci\JWT\Token;
use Phper666\JWTAuth\Util\JWTUtil;
use Hyperf\Cache\Annotation\Cacheable;
use InvalidArgumentException;

class TokenService extends AbstractService implements TokenServiceInterface
{
    #[Inject]
    protected RequestInterface $request;

    public function getToken(): string
    {
        // 正常请求时获取Request对象
        if (!empty($this->request)) {
            $token = $this->request->getHeaderLine('Authorization') ?? '';
        }
        if (empty($token)) {    // 在自定义进程中，没有Request对象，需要自行设置
            $token = Context::get('Authorization') ?? '';
        }
        return $token;
    }

    public function parseToken(): Token
    {
        $token = Context::get('parseToken');
        if (!empty($token)) return $token;
        $token = $this->getToken();
        if (empty($token)) {
            throw new BusinessException(ErrorCode::AUTH_UNAUTHORIZED);
        }
        $token = JWTUtil::handleToken($this->getToken());
        if (!$token) {
            throw new BusinessException(ErrorCode::AUTH_FAILED);
        }
        try {
            $token = JWTUtil::getParser()->parse($token);
        } catch (InvalidArgumentException $e) {
            throw new BusinessException(ErrorCode::AUTH_FAILED);
        } catch (\Exception $e) {
            throw new BusinessException(-1, $e->getMessage());
        }
        if ($token->isExpired(new DateTime())) {
            throw new BusinessException(ErrorCode::AUTH_SESSION_EXPIRED);
        }
        Context::set('parseToken', $token);
        return $token;
    }

    public function getUid(): int
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("id");
    }

    public function getUsername(): string
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("name");
    }

    #[ArrayShape(['uid' => "mixed", 'name' => "mixed"])]
    public function getUidAndUsername(): array
    {
        $tokenObj = $this->parseToken();
        $id = $tokenObj->claims()->get("id");
        $username = $tokenObj->claims()->get("name");
        return ['uid' => $id, 'name' => $username];
    }


    public function getRoleInfo(): array
    {
        $tokenObj = $this->parseToken();
        $roleInfo = $tokenObj->claims()->get("roleInfo");
        return (array)$roleInfo;
    }

    public function getDataPermission($type): array
    {
        $tokenObj = $this->parseToken();
        $roleInfo = $tokenObj->claims()->get("roleInfo");
        return $this->getContainer()->get(RoleDataPermissions::class)->dataPermission($roleInfo->id, $type);
    }


    public function isSuperAdmin(): bool
    {
        if (1 == $this->getRoleInfo()['id']) {
            return true;
        }
        return false;
    }

    public function getPermissionInfo(): array
    {
        $tokenObj = $this->parseToken();
        $permissionInfo = $tokenObj->claims()->get("permissionInfo");
        return Permissions::query()->find($permissionInfo)?->toArray();
    }


    public function getPermissionIds(): array
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("permissionInfo");
    }

    public function getDepartmentName(): string
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("department");
    }

    public function getDepartmentId(): string
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("department_id");
    }

    public function getEmail()
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("email");
    }

    public function getJti(): string
    {
        $tokenObj = $this->parseToken();
        return $tokenObj->claims()->get("jti");
    }


}
