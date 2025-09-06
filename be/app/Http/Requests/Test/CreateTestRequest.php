<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

class CreateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}