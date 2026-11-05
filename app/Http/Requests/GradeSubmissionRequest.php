<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $submission && $this->user()?->can('grade', $submission);
    }

    public function rules(): array
    {
        return [
            'grade' => ['required', 'integer', 'between:0,100'],
            'teacher_feedback' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
