<?php

declare(strict_types=1);

namespace App\Request\Users;

use Hyperf\Context\Context;
use Hyperf\Validation\Request\FormRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * 表单验证器
 */
class UserUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        return [
            'id' => 'required|integer',
            'name' => 'string|between:1,255',
            'username' => 'string|between:1,255',
            'password' => 'string|between:6,50',
            'email' => 'string|email',
            'role_id' => 'integer',
            'status' => 'integer|between:0,1',
            'idstr' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'password.between' => '密码长度必须为6~50个字符'
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     */
    public function response(): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);

        return $response->withStatus(423);
    }
}