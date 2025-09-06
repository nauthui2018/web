<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'sometimes|required|string',
            'question_type' => 'sometimes|required|in:multiple_choice,true_false,short_answer,essay',
            'options' => 'sometimes|required_if:question_type,multiple_choice|array',
            'options.*' => 'required_with:options|string',
            'correct_answer' => 'sometimes|required|string',
            'points' => 'sometimes|integer|min:1|max:100',
            'order' => 'sometimes|integer|min:1',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCodes::VALIDATION_ERROR,
                'Question update validation failed',
                'Please check your input and try again',
                $validator->errors()->toArray()
            )
        );
    }
}