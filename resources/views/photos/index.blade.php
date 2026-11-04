@extends('layouts.app')

@section('title', 'All Photos')

@section('content')
<div class="mb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-image text-purple-600 dark:text-purple-400"></i> All Photos
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and filter all your uploaded photos</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('photos.create') }}" class="bg-purple-600 dark:bg-purple-500 text-white px-6 py-3 rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-md inline-block">
                <i class="fas fa-upload mr-2"></i> Upload Photo
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition">
        <form action="{{ route('photos.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-search"></i> Search
                    </label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Search by title or description..."
                    >
                </div>

                <div>
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-folder"></i> Folder
                    </label>
                    <select name="folder_id" id="folder_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Folders</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end space-x-3">
                    <button type="submit" class="px-6 py-2 bg-purple-600 dark:bg-purple-500 text-white rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-md">
                        <i class="fas fa-filter mr-2"></i> Apply
                    </button>
                    <a href="{{ route('photos.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden dark-mode-transition">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Photos ({{ $photos->total() }})
            </h2>
            @if(request()->hasAny(['search', 'folder_id']))
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-filter text-purple-600 dark:text-purple-400"></i> Filtered
                </span>
            @endif
        </div>

        @if($photos->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-image text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Photos Found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if(request()->hasAny(['search', 'folder_id']))
                        Try adjusting your filters or search terms
                    @else
                        Start uploading photos to keep them organized
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'folder_id']))
                    <a href="{{ route('photos.create') }}" class="bg-purple-600 dark:bg-purple-500 text-white px-6 py-2.5 rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-md inline-block">
                        <i class="fas fa-upload mr-2"></i> Upload First Photo
                    </a>
                @endif
            </div>
        @else
            <div class="p-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($photos as $photo)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group border border-gray-100 dark:border-gray-700">
                        <a href="{{ route('photos.show', $photo->id) }}" class="block aspect-square overflow-hidden bg-gray-100 dark:bg-gray-900">
                            <img src="{{ $photo->url }}" alt="{{ $photo->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        </a>
                        <div class="p-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm truncate mb-1">
                                <a href="{{ route('photos.show', $photo->id) }}" class="hover:text-purple-600 dark:hover:text-purple-400">{{ $photo->title }}</a>
                            </h4>
                            <a href="{{ route('folders.show', $photo->folder_id) }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white mb-2 hover:opacity-80 transition" style="background-color: {{ $photo->folder->color_code }};">
                                <i class="fas fa-folder mr-1"></i> {{ $photo->folder->name }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $photo->formatted_size }}</p>
                            <div class="flex justify-between items-center">
                                <a href="{{ $photo->url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-xs" title="View Full Size">
                                    <i class="fas fa-expand-alt"></i>
                                </a>
                                <div class="flex space-x-3">
                                    <a href="{{ route('photos.show', $photo->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 text-xs" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('photos.edit', $photo->id) }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 text-xs" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this photo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($photos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $photos->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
