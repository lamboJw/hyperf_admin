<?php

declare(strict_types=1);

namespace App\Request\Permissions;

use Hyperf\Context\Context;
use Hyperf\Validation\Request\FormRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * 表单验证器
 */
class PermissionsAddRequest extends FormRequest {
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
            'name' => 'required|string|unique:permissions|between:1,255',
            'path' => 'required|string|unique:permissions|between:1,255',
            'parent_id' => 'integer',
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
