@extends('layouts.app')

@section('title', $photo->title)

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <a href="{{ route('photos.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Back to Photos
        </a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('folders.show', $photo->folder_id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-folder mr-1"></i> {{ $photo->folder->name }}
        </a>
    </nav>

    <!-- Photo Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition" style="border-left: 6px solid {{ $photo->folder->color_code }};">
        <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2 break-words">{{ $photo->title }}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="far fa-calendar mr-1"></i> Uploaded {{ $photo->created_at->format('M d, Y') }}</span>
                    <span class="mx-2">&middot;</span>
                    <span>{{ $photo->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ $photo->url }}" target="_blank" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Open Full Size">
                    <i class="fas fa-expand-alt"></i>
                </a>
                <a href="{{ route('photos.edit', $photo->id) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this photo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400 px-4 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Photo Preview -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 dark-mode-transition">
            <a href="{{ $photo->url }}" target="_blank" class="block bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden">
                <img src="{{ $photo->url }}" alt="{{ $photo->title }}" class="w-full h-auto max-h-[70vh] object-contain mx-auto">
            </a>
        </div>

        <!-- Details -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-info-circle text-purple-600 dark:text-purple-400 mr-2"></i> File Details
                </h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Original name</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-right break-all">{{ $photo->filename }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-right">{{ $photo->mime_type ?: 'Unknown' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">Size</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-right">{{ $photo->formatted_size }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">
                    <i class="fas fa-folder text-blue-500 dark:text-blue-400 mr-2"></i> Folder
                </h2>
                <a href="{{ route('folders.show', $photo->folder_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-white hover:opacity-80 transition" style="background-color: {{ $photo->folder->color_code }};">
                    {{ $photo->folder->name }}
                </a>
            </div>

            @if($photo->description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-align-left text-yellow-500 dark:text-yellow-400 mr-2"></i> Description
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm whitespace-pre-line">{{ $photo->description }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
