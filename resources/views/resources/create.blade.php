@extends('layouts.app')

@section('title', 'Add Resource')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <nav>
            <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('resources.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </nav>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">New bookmark</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Add New Resource</h1>
            <p class="mt-2 text-sm text-base-content/60">Keep links and study documents alongside your private notes.</p>
        </div>

        <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data" class="app-surface space-y-6 p-5 sm:p-6">
            @csrf

            <div>
                <label for="folder_id" class="mb-2 block text-sm font-medium">Folder <span class="text-error">*</span></label>
                <select name="folder_id" id="folder_id" class="select select-bordered w-full @error('folder_id') select-error @enderror" required>
                    <option value="">Choose a folder</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $selectedFolderId) == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="mb-2 block text-sm font-medium">Resource title <span class="text-error">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
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
                    <input type="url" name="url" id="url" value="{{ old('url') }}" class="grow" placeholder="https://example.com/resource">
                </label>
                @error('url')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="resource_type" class="mb-2 block text-sm font-medium">Resource type <span class="text-error">*</span></label>
                    <select name="resource_type" id="resource_type" class="select select-bordered w-full @error('resource_type') select-error @enderror" required>
                        <option value="url" {{ old('resource_type', 'url') == 'url' ? 'selected' : '' }}>Link / article</option>
                        <option value="file" {{ old('resource_type') == 'file' ? 'selected' : '' }}>PDF / document</option>
                        <option value="documentation" {{ old('resource_type') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="video" {{ old('resource_type') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="assignment_instruction" {{ old('resource_type') == 'assignment_instruction' ? 'selected' : '' }}>Assignment instructions</option>
                    </select>
                    @error('resource_type')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div id="file-field" @class(['hidden' => old('resource_type', 'url') !== 'file'])>
                    <label for="files" class="mb-2 block text-sm font-medium">Upload materials <span class="text-base-content/45">(PDF, Word, or PowerPoint · up to 10 files · max 20 MB each)</span></label>
                    <input type="file" name="files[]" id="files" accept=".pdf,.doc,.docx,.ppt,.pptx" multiple class="file-input file-input-bordered w-full @error('files.*') file-input-error @enderror" data-file-picker>
                    @error('files')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    @error('files.*')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="read_status" class="mb-2 block text-sm font-medium">Reading status <span class="text-error">*</span></label>
                    <select name="read_status" id="read_status" class="select select-bordered w-full @error('read_status') select-error @enderror" required>
                        <option value="to_read" {{ old('read_status') == 'to_read' ? 'selected' : '' }}>To Read</option>
                        <option value="reading" {{ old('read_status') == 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="completed" {{ old('read_status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                          placeholder="Add personal notes, key takeaways, or reminders...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-base-content/45">Optional — remember why this resource is worth keeping.</p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-base-300/70 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('resources.index') }}" class="btn btn-ghost border-base-300">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Resource</button>
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
