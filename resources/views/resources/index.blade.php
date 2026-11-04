@extends('layouts.app')

@section('title', 'All Resources')

@section('content')
<div class="mb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-bookmark text-blue-600 dark:text-blue-400"></i> All Resources
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and filter all your saved resources</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('resources.create') }}" class="bg-blue-600 dark:bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md inline-block">
                <i class="fas fa-plus mr-2"></i> Add Resource
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition">
        <form action="{{ route('resources.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-search"></i> Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        id="search" 
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Search by title, notes, or URL..."
                    >
                </div>
                
                <!-- Folder Filter -->
                <div>
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-folder"></i> Folder
                    </label>
                    <select 
                        name="folder_id" 
                        id="folder_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Folders</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Resource Type Filter -->
                <div>
                    <label for="resource_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-tag"></i> Type
                    </label>
                    <select 
                        name="resource_type" 
                        id="resource_type"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Types</option>
                        <option value="documentation" {{ request('resource_type') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="article" {{ request('resource_type') == 'article' ? 'selected' : '' }}>Article</option>
                        <option value="repository" {{ request('resource_type') == 'repository' ? 'selected' : '' }}>Repository</option>
                        <option value="video" {{ request('resource_type') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="other" {{ request('resource_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Read Status Filter -->
                <div>
                    <label for="read_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-check-circle"></i> Status
                    </label>
                    <select 
                        name="read_status" 
                        id="read_status"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Statuses</option>
                        <option value="to_read" {{ request('read_status') == 'to_read' ? 'selected' : '' }}>To Read</option>
                        <option value="reading" {{ request('read_status') == 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="completed" {{ request('read_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <!-- Filter Actions -->
                <div class="md:col-span-3 flex items-end space-x-3">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('resources.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
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
                Resources ({{ $resources->total() }})
            </h2>
            @if(request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-filter text-blue-600 dark:text-blue-400"></i> Filtered
                </span>
            @endif
        </div>
        
        @if($resources->isEmpty())
            <!-- Empty State -->
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Resources Found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if(request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                        Try adjusting your filters or search terms
                    @else
                        Start adding resources to get started
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                    <a href="{{ route('resources.create') }}" class="bg-blue-600 dark:bg-blue-500 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md inline-block">
                        <i class="fas fa-plus mr-2"></i> Add First Resource
                    </a>
                @endif
            </div>
        @else
            <!-- Resources List -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($resources as $resource)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <div class="flex-1 mb-4 lg:mb-0">
                                <!-- Title & URL -->
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $resource->title }}
                                </h3>
                                <a href="{{ $resource->url }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 break-all mb-3 inline-block">
                                    <i class="fas fa-external-link-alt mr-1"></i> {{ Str::limit($resource->url, 80) }}
                                </a>
                                
                                <!-- Folder Badge -->
                                <div class="mb-2">
                                    <a href="{{ route('folders.show', $resource->folder->id) }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white hover:opacity-80 transition" style="background-color: {{ $resource->folder->color_code }};">
                                        <i class="fas fa-folder mr-1"></i> {{ $resource->folder->name }}
                                    </a>
                                </div>
                                
                                <!-- Notes Preview -->
                                @if($resource->notes)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        <i class="fas fa-sticky-note text-yellow-500 dark:text-yellow-400 mr-1"></i> {{ Str::limit($resource->notes, 150) }}
                                    </p>
                                @endif
                                
                                <!-- Meta Info -->
                                <div class="flex items-center space-x-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span><i class="far fa-calendar"></i> {{ $resource->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            <!-- Badges & Actions -->
                            <div class="flex flex-col items-start lg:items-end space-y-3 lg:ml-6">
                                <!-- Type & Status Badges -->
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $resource->type_badge_color }}">
                                        {{ ucfirst($resource->resource_type) }}
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $resource->status_badge_color }}">
                                        {{ ucfirst(str_replace('_', ' ', $resource->read_status)) }}
                                    </span>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('folders.show', $resource->folder_id) }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition" title="View Folder">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <a href="{{ route('resources.edit', $resource->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($resources->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $resources->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
