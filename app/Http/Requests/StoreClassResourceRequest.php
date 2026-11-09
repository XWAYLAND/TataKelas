<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folder = $this->route('folder');

        return $folder && $this->user()?->can('create', [\App\Models\Resource::class, $folder]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'resource_type' => ['required', Rule::in(['url', 'file', 'video', 'documentation', 'assignment_instruction'])],
            'url' => ['nullable', 'required_unless:resource_type,file', 'url', 'max:2048'],
            'files' => ['nullable', 'array', 'max:10', 'required_if:resource_type,file'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
