<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $classroom = $this->route('classroom');

        return $classroom && $this->user()?->can('createClassFolder', $classroom);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color_code' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'visibility' => ['required', Rule::in(['class_material', 'assignment'])],
            'due_date' => ['required_if:visibility,assignment', 'nullable', 'date'],
        ];
    }
}
