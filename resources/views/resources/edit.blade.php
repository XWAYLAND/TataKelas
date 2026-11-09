@extends('layouts.app')

@section('title', 'Edit Resource')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <nav>
            <a href="{{ route('folders.show', $resource->folder_id) }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to folder
            </a>
        </nav>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Resource settings</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Edit Resource</h1>
            <p class="mt-2 text-sm text-base-content/60">Update the link, category, reading status, or personal notes.</p>
        </div>

        <form action="{{ route('resources.update', $resource->id) }}" method="POST" enctype="multipart/form-data" class="app-surface space-y-6 p-5 sm:p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="folder_id" class="mb-2 block text-sm font-medium">Folder <span class="text-error">*</span></label>
                <select name="folder_id" id="folder_id" class="select select-bordered w-full @error('folder_id') select-error @enderror" required>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $resource->folder_id) == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="mb-2 block text-sm font-medium">Resource title <span class="text-error">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $resource->title) }}"
                       class="input input-bordered w-full @error('title') input-error @enderror"
                       placeholder="e.g., Laravel 11 Documentation, React Hooks Guide" required autofocus>
                @error('title')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="url" class="mb-2 block text-sm font-medium">URL <span class="text-base-content/45">(leave blank for a file)</span></label>
                <label class="input input-bordered flex w-full items-center gap-2 @error('url') input-error @enderror">
                    <i class="fas fa-link text-xs text-base-content/40"></i>
                    <input type="url" name="url" id="url" value="{{ old('url', $resource->url) }}" class="grow" placeholder="https://example.com/resource">
                </label>
                @error('url')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="resource_type" class="mb-2 block text-sm font-medium">Resource type <span class="text-error">*</span></label>
                    <select name="resource_type" id="resource_type" class="select select-bordered w-full @error('resource_type') select-error @enderror" required>
                        <option value="url" {{ old('resource_type', $resource->resource_type) == 'url' ? 'selected' : '' }}>Link / article</option>
                        <option value="file" {{ old('resource_type', $resource->resource_type) == 'file' ? 'selected' : '' }}>PDF / document</option>
                        <option value="documentation" {{ old('resource_type', $resource->resource_type) == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="video" {{ old('resource_type', $resource->resource_type) == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="assignment_instruction" {{ old('resource_type', $resource->resource_type) == 'assignment_instruction' ? 'selected' : '' }}>Assignment instructions</option>
                    </select>
                    @error('resource_type')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div id="file-field" @class(['hidden' => old('resource_type', $resource->resource_type) !== 'file'])>
                    <label for="files" class="mb-2 block text-sm font-medium">Replace attached materials <span class="text-base-content/45">(optional · up to 10 files · max 20 MB each)</span></label>
                    <input type="file" name="files[]" id="files" accept=".pdf,.doc,.docx,.ppt,.pptx" multiple class="file-input file-input-bordered w-full @error('files.*') file-input-error @enderror" data-file-picker>
                    @if($resource->files->isNotEmpty())
                        <div class="mt-2 space-y-1 text-xs text-base-content/55">
                            <p class="font-medium text-base-content/70">Currently attached:</p>
                            <ul class="ml-4 list-disc">
                                @foreach($resource->files as $file)
                                    <li>
                                        {{ $file->original_name }}
                                        @if($file->is_previewable)
                                            · <a class="link" href="{{ route('resources.files.preview', [$resource, $file]) }}" target="_blank" rel="noopener">preview</a>
                                        @endif
                                        · <a class="link" href="{{ route('resources.files.download', [$resource, $file]) }}">download</a>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-1">Uploading new files below replaces all of them.</p>
                        </div>
                    @endif
                    @error('files')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    @error('files.*')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="read_status" class="mb-2 block text-sm font-medium">Reading status <span class="text-error">*</span></label>
                    <select name="read_status" id="read_status" class="select select-bordered w-full @error('read_status') select-error @enderror" required>
                        <option value="to_read" {{ old('read_status', $resource->read_status) == 'to_read' ? 'selected' : '' }}>To Read</option>
                        <option value="reading" {{ old('read_status', $resource->read_status) == 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="completed" {{ old('read_status', $resource->read_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('read_status')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="notes" class="mb-2 block text-sm font-medium">Notes</label>
                <textarea name="notes" id="notes" rows="4"
                          class="textarea textarea-bordered w-full @error('notes') textarea-error @enderror"
                          placeholder="Add personal notes, key takeaways, or reminders...">{{ old('notes', $resource->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-base-300/70 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('folders.show', $resource->folder_id) }}" class="btn btn-ghost border-base-300">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('partials.file-picker')
    <script>
        (function () {
            var typeSelect = document.getElementById('resource_type');
            var fileField = document.getElementById('file-field');
            if (!typeSelect || !fileField) return;

            function toggleFileField() {
                fileField.classList.toggle('hidden', typeSelect.value !== 'file');
            }

            typeSelect.addEventListener('change', toggleFileField);
            toggleFileField();
        })();
    </script>
@endpush
