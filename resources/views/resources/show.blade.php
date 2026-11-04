@extends('layouts.app')

@section('title', $resource->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <a href="{{ route('resources.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Back to Resources
        </a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('folders.show', $resource->folder_id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-folder mr-1"></i> {{ $resource->folder->name }}
        </a>
    </nav>

    <!-- Resource Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition" style="border-left: 6px solid {{ $resource->folder->color_code }};">
        <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $resource->type_badge_color }}">
                        {{ ucfirst($resource->resource_type) }}
                    </span>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $resource->status_badge_color }}">
                        {{ ucfirst(str_replace('_', ' ', $resource->read_status)) }}
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2 break-words">{{ $resource->title }}</h1>

                <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 break-all">
                    <i class="fas fa-external-link-alt mr-1"></i> {{ $resource->url }}
                </a>

                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="far fa-calendar mr-1"></i> Added {{ $resource->created_at->format('M d, Y') }}</span>
                    <span class="mx-2">&middot;</span>
                    <span>{{ $resource->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('resources.edit', $resource->id) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this resource?');">
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
        <!-- Notes -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-sticky-note text-yellow-500 dark:text-yellow-400 mr-2"></i> Notes
            </h2>
            @if($resource->notes)
                <p class="text-gray-600 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $resource->notes }}</p>
            @else
                <p class="text-gray-400 dark:text-gray-500 italic">No notes added for this resource.</p>
            @endif
        </div>

        <!-- Details & Actions -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-folder text-blue-500 dark:text-blue-400 mr-2"></i> Folder
                </h2>
                <a href="{{ route('folders.show', $resource->folder_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-white hover:opacity-80 transition" style="background-color: {{ $resource->folder->color_code }};">
                    {{ $resource->folder->name }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-check-circle text-green-500 dark:text-green-400 mr-2"></i> Reading Status
                </h2>
                <form action="{{ route('resources.update-status', $resource->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="read_status" onchange="this.form.submit()" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer">
                        <option value="to_read" {{ $resource->read_status === 'to_read' ? 'selected' : '' }}>To Read</option>
                        <option value="reading" {{ $resource->read_status === 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="completed" {{ $resource->read_status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </form>
            </div>

            <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="block w-full text-center bg-blue-600 dark:bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md">
                <i class="fas fa-external-link-alt mr-2"></i> Open Resource
            </a>
        </div>
    </div>
</div>
@endsection
