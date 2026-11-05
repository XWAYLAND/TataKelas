@extends('layouts.app')

@section('title', 'Edit Photo')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <nav>
            <a href="{{ route('folders.show', $photo->folder_id) }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to folder
            </a>
        </nav>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-secondary">Photo settings</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Edit Photo</h1>
            <p class="mt-2 text-sm text-base-content/60">Update the title, description, folder, or replace the image file.</p>
        </div>

        <form action="{{ route('photos.update', $photo->id) }}" method="POST" enctype="multipart/form-data" class="app-surface space-y-6 p-5 sm:p-6">
            @csrf
            @method('PUT')

            <div>
                <p class="mb-2 block text-sm font-medium">Current photo</p>
                <div class="rounded-xl border border-base-300/80 bg-base-200/40 p-4">
                    <img src="{{ $photo->url }}" alt="{{ $photo->title }}" class="mx-auto max-h-64 rounded-lg shadow-md">
                </div>
            </div>

            <div>
                <label for="folder_id" class="mb-2 block text-sm font-medium">Folder <span class="text-error">*</span></label>
                <select name="folder_id" id="folder_id" class="select select-bordered w-full @error('folder_id') select-error @enderror" required>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $photo->folder_id) == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="mb-2 block text-sm font-medium">Photo title <span class="text-error">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $photo->title) }}"
                       class="input input-bordered w-full @error('title') input-error @enderror"
                       placeholder="e.g., Screenshot, Diagram, Wireframe" required autofocus>
                @error('title')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="photo" class="mb-2 block text-sm font-medium">Replace photo <span class="text-base-content/40">(optional)</span></label>
                <input type="file" name="photo" id="photo" accept="image/*" class="sr-only" onchange="previewImage(event)">
                <label for="photo" class="block cursor-pointer rounded-xl border-2 border-dashed border-base-300 bg-base-200/30 p-6 text-center transition hover:border-primary hover:bg-primary/5 @error('photo') border-error @enderror">
                    <div id="preview-container" class="mb-4 hidden">
                        <img id="image-preview" src="" alt="Preview" class="mx-auto max-h-64 rounded-lg shadow-md">
                    </div>
                    <div id="upload-placeholder">
                        <i class="fas fa-cloud-arrow-up text-3xl text-base-content/30"></i>
                        <p class="mt-2 font-medium">Click to choose a replacement</p>
                        <p class="mt-1 text-xs text-base-content/50">Leave empty to keep the current photo.</p>
                    </div>
                </label>
                @error('photo')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                          placeholder="Add a description for this photo...">{{ old('description', $photo->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-base-300/70 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('folders.show', $photo->folder_id) }}" class="btn btn-ghost border-base-300">Cancel</a>
                <button type="submit" class="btn btn-secondary"><i class="fas fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    </script>
@endpush
