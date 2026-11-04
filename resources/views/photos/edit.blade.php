@extends('layouts.app')

@section('title', 'Edit Photo')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('folders.show', $photo->folder_id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-edit text-purple-600 dark:text-purple-400"></i> Edit Photo
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Update photo information</p>
    </div>
    
    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 dark-mode-transition">
        <form action="{{ route('photos.update', $photo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Current Photo Preview -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Current Photo
                </label>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                    <img src="{{ $photo->url }}" alt="{{ $photo->title }}" class="max-h-64 mx-auto rounded-lg shadow-md">
                </div>
            </div>
            
            <!-- Folder Selection -->
            <div class="mb-6">
                <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Folder <span class="text-red-500">*</span>
                </label>
                <select 
                    name="folder_id" 
                    id="folder_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('folder_id') border-red-500 @enderror"
                    required
                >
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $photo->folder_id) == $folder->id ? 'selected' : '' }}>
                            {{ $folder->name }}
                        </option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Photo Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Photo Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title', $photo->title) }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                    placeholder="e.g., Screenshot, Diagram, Wireframe"
                    required
                    autofocus
                >
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Photo Upload (Optional for Edit) -->
            <div class="mb-6">
                <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Replace Photo (Optional)
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 transition">
                    <input 
                        type="file" 
                        name="photo" 
                        id="photo" 
                        accept="image/*"
                        class="hidden"
                        onchange="previewImage(event)"
                    >
                    <label for="photo" class="cursor-pointer">
                        <div id="preview-container" class="hidden mb-4">
                            <img id="image-preview" src="" alt="Preview" class="mx-auto max-h-64 rounded-lg shadow-md">
                        </div>
                        <div id="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 dark:text-gray-500 mb-3"></i>
                            <p class="text-gray-600 dark:text-gray-300 font-medium mb-2">Click to upload new photo</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</p>
                        </div>
                    </label>
                </div>
                @error('photo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Leave empty to keep the current photo</p>
            </div>
            
            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                    placeholder="Add a description for this photo..."
                >{{ old('description', $photo->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t dark:border-gray-700">
                <a href="{{ route('folders.show', $photo->folder_id) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 dark:bg-purple-500 text-white rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-md">
                    <i class="fas fa-save mr-2"></i> Update Photo
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection
