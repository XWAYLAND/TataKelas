@extends('layouts.app')

@section('title', 'Create Note')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('notes.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-sticky-note text-yellow-600 dark:text-yellow-400"></i> Create New Note
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Add a new note to organize your thoughts and ideas</p>
    </div>
    
    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 dark-mode-transition">
        <form action="{{ route('notes.store') }}" method="POST">
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
            
            <!-- Note Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Note Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                    placeholder="e.g., Project Ideas, Meeting Notes, Study Summary"
                    required
                    autofocus
                >
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Priority Level -->
            <div class="mb-6">
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Priority Level <span class="text-red-500">*</span>
                </label>
                <select 
                    name="priority" 
                    id="priority"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('priority') border-red-500 @enderror"
                    required
                >
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                </select>
                @error('priority')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Note Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Note Content <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="content" 
                    id="content" 
                    rows="12"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('content') border-red-500 @enderror"
                    placeholder="Write your note content here...

You can add:
- Lists
- Code snippets
- Ideas
- Reminders
- Anything you need to remember!"
                    required
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    <i class="fas fa-info-circle"></i> Write your thoughts, ideas, or important information
                </p>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t dark:border-gray-700">
                <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('notes.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-yellow-600 dark:bg-yellow-700 text-white rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition shadow-md">
                    <i class="fas fa-save mr-2"></i> Save Note
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
