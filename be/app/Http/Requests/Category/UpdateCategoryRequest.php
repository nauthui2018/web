<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');
        
        return [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $categoryId,
            'description' => 'sometimes|required|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCodes::VALIDATION_ERROR,
                'Category update validation failed',
                'Please check your input and try again',
                $validator->errors()->toArray()
            )
        );
    }
}