<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId . ',id,deleted_at,NULL',
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:user,teacher,admin',
            'is_active' => 'sometimes|boolean',
            'phone' => 'nullable|string|max:20',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCodes::VALIDATION_ERROR,
                'User update validation failed',
                'Please check your input and try again',
                $validator->errors()->toArray()
            )
        );
    }
}
