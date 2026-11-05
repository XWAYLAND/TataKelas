<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JoinClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['student', 'teacher', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', Rule::exists('classrooms', 'code')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => Str::upper(trim((string) $this->input('code')))]);
    }
}
