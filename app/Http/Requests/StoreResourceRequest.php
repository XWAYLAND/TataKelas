<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'folder_id' => ['required', 'integer', 'exists:folders,id'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'resource_type' => ['required', Rule::in(['documentation', 'article', 'repository', 'video', 'other'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'read_status' => ['required', Rule::in(['to_read', 'reading', 'completed'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'folder_id' => 'folder',
            'title' => 'resource title',
            'url' => 'resource URL',
            'resource_type' => 'resource type',
            'notes' => 'notes',
            'read_status' => 'read status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'folder_id.required' => 'Please select a folder.',
            'folder_id.exists' => 'The selected folder does not exist.',
            'title.required' => 'The resource title is required.',
            'title.max' => 'The resource title must not exceed 255 characters.',
            'url.required' => 'The resource URL is required.',
            'url.url' => 'Please enter a valid URL (e.g., https://example.com).',
            'url.max' => 'The URL must not exceed 2048 characters.',
            'resource_type.required' => 'Please select a resource type.',
            'resource_type.in' => 'Invalid resource type selected.',
            'read_status.required' => 'Please select a read status.',
            'read_status.in' => 'Invalid read status selected.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        if (!$this->has('resource_type')) {
            $this->merge(['resource_type' => 'article']);
        }

        if (!$this->has('read_status')) {
            $this->merge(['read_status' => 'to_read']);
        }
    }
}
