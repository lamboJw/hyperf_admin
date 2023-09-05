<?php

declare(strict_types=1);

namespace App\Request\Permissions;

use Hyperf\Context\Context;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;
use Psr\Http\Message\ResponseInterface;

/**
 * 表单验证器
 */
class PermissionsUpdateRequest extends FormRequest {
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
            'name' => 'string|between:1,255',
            'path' => [
                'string',
                'between:1,255',
                Rule::unique('permissions')->ignore($this->input('id'))
            ],
            'id' => 'required|integer',
            'recursion' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'path.unique' => '路径已存在',
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
