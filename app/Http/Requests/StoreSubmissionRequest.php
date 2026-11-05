<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folder = $this->route('folder');

        return $folder && $this->user()?->can('submit', $folder);
    }

    public function rules(): array
    {
        return [
            'submission_text' => ['nullable', 'string', 'max:10000', 'required_without_all:file,submitted_url'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:20480', 'required_without_all:submission_text,submitted_url'],
            'submitted_url' => ['nullable', 'url', 'max:2048', 'required_without_all:submission_text,file'],
        ];
    }
}
