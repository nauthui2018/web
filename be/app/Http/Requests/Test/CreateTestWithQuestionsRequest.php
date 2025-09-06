<?php

namespace App\Http\Requests\Test;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Helpers\ResponseHelper;
use App\Constants\ErrorCodes;

class CreateTestWithQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Test information
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'show_correct_answer' => 'boolean',
            'passing_score' => 'nullable|numeric|between:0,100|regex:/^\d+(\.\d{1,2})?$/',
            'difficulty_level' => 'nullable|in:Beginner,Intermediate,Advanced',

            // Questions array
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:multiple_choice,multiple_select,true_false,short_answer,essay',

            // Options required for multiple choice types
            'questions.*.options' => 'required_if:questions.*.question_type,multiple_choice,multiple_select|array',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
            'questions.*.options.*.id' => 'sometimes|integer',

            'questions.*.points' => 'integer|min:1|max:100',
            'questions.*.order' => 'integer|min:1',
        ];
    }


    public function messages(): array
    {
        return [
            'questions.required' => 'At least one question is required.',
            'questions.min' => 'At least one question is required.',
            'questions.*.question_text.required' => 'Question text is required for all questions.',
            'questions.*.question_type.required' => 'Question type is required for all questions.',
            'questions.*.question_type.in' => 'Question type must be one of: multiple_choice, multiple_select, true_false, short_answer, essay.',
            'questions.*.options.required_if' => 'Options are required for multiple choice questions.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCodes::VALIDATION_ERROR,
                'Test creation validation failed',
                'Please check your input and try again',
                $validator->errors()->toArray()
            )
        );
    }
}
