<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'required|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCodes::VALIDATION_ERROR,
                'Category creation validation failed',
                'Please check your input and try again',
                $validator->errors()->toArray()
            )
        );
    }
}