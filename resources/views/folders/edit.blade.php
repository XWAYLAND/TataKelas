@extends('layouts.app')

@section('title', 'Edit Folder')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('folders.show', $folder->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to Folder
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-edit text-blue-600 dark:text-blue-400"></i> Edit Folder
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Update folder information</p>
    </div>
    
    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 dark-mode-transition">
        <form action="{{ route('folders.update', $folder->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Folder Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Folder Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $folder->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                    placeholder="e.g., Laravel Documentation, React Tutorials"
                    required
                    autofocus
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
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
                    placeholder="Brief description of what this folder contains..."
                >{{ old('description', $folder->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Color Code -->
            <div class="mb-6">
                <label for="color_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Folder Color
                </label>
                <div class="flex items-center space-x-4">
                    <input 
                        type="color" 
                        name="color_code" 
                        id="color_code" 
                        value="{{ old('color_code', $folder->color_code) }}"
                        class="h-12 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer"
                    >
                    <input 
                        type="text" 
                        id="color_code_text" 
                        value="{{ old('color_code', $folder->color_code) }}"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="#3B82F6"
                        readonly
                    >
                </div>
                @error('color_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <!-- Preset Colors -->
                <div class="flex items-center space-x-2 mt-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Quick picks:</span>
                    <button type="button" onclick="setColor('#3B82F6')" class="w-8 h-8 rounded-full bg-blue-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                    <button type="button" onclick="setColor('#10B981')" class="w-8 h-8 rounded-full bg-green-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                    <button type="button" onclick="setColor('#F59E0B')" class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                    <button type="button" onclick="setColor('#EF4444')" class="w-8 h-8 rounded-full bg-red-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                    <button type="button" onclick="setColor('#8B5CF6')" class="w-8 h-8 rounded-full bg-purple-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                    <button type="button" onclick="setColor('#EC4899')" class="w-8 h-8 rounded-full bg-pink-500 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-300 transition"></button>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t dark:border-gray-700">
                <a href="{{ route('folders.show', $folder->id) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md">
                    <i class="fas fa-save mr-2"></i> Update Folder
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Sync color picker with text input
    const colorPicker = document.getElementById('color_code');
    const colorText = document.getElementById('color_code_text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value.toUpperCase();
    });
    
    function setColor(color) {
        colorPicker.value = color;
        colorText.value = color;
    }
</script>
@endpush
@endsection
