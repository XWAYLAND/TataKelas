@extends('layouts.app')

@section('title', 'Add Resource')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('resources.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400"></i> Add New Resource
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Add a new bookmark, article, or documentation link</p>
    </div>
    
    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 dark-mode-transition">
        <form action="{{ route('resources.store') }}" method="POST">
            @csrf
            
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
                    <option value="">-- Choose a folder --</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $selectedFolderId) == $folder->id ? 'selected' : '' }}>
                            {{ $folder->name }}
                        </option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Resource Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Resource Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                    placeholder="e.g., Laravel 11 Documentation, React Hooks Guide"
                    required
                    autofocus
                >
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- URL -->
            <div class="mb-6">
                <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    URL <span class="text-red-500">*</span>
                </label>
                <input 
                    type="url" 
                    name="url" 
                    id="url" 
                    value="{{ old('url') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('url') border-red-500 @enderror"
                    placeholder="https://example.com/resource"
                    required
                >
                @error('url')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Resource Type & Read Status (Two Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Resource Type -->
                <div>
                    <label for="resource_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Resource Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="resource_type" 
                        id="resource_type"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('resource_type') border-red-500 @enderror"
                        required
                    >
                        <option value="article" {{ old('resource_type') == 'article' ? 'selected' : '' }}>Article</option>
                        <option value="documentation" {{ old('resource_type') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="repository" {{ old('resource_type') == 'repository' ? 'selected' : '' }}>Repository</option>
                        <option value="video" {{ old('resource_type') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="other" {{ old('resource_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('resource_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Read Status -->
                <div>
                    <label for="read_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Read Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="read_status" 
                        id="read_status"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('read_status') border-red-500 @enderror"
                        required
                    >
                        <option value="to_read" {{ old('read_status') == 'to_read' ? 'selected' : '' }}>To Read</option>
                        <option value="reading" {{ old('read_status') == 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="completed" {{ old('read_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('read_status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Notes
                </label>
                <textarea 
                    name="notes" 
                    id="notes" 
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                    placeholder="Add personal notes, key takeaways, or reminders..."
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Optional: Add notes to help you remember why you saved this resource</p>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t dark:border-gray-700">
                <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('resources.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md">
                    <i class="fas fa-save mr-2"></i> Save Resource
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
