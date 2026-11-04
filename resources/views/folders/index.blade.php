@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-th-large text-blue-600 dark:text-blue-400"></i> My Folders
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Organize your learning resources by workspace folders</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('folders.create') }}" class="bg-blue-600 dark:bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md inline-block">
                <i class="fas fa-plus mr-2"></i> Create New Folder
            </a>
        </div>
    </div>
    
    @if($folders->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center dark-mode-transition">
            <i class="fas fa-folder-open text-gray-300 dark:text-gray-600 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No Folders Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first folder to start organizing your resources</p>
            <a href="{{ route('folders.create') }}" class="bg-blue-600 dark:bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md inline-block">
                <i class="fas fa-plus mr-2"></i> Create First Folder
            </a>
        </div>
    @else
        <!-- Folders Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($folders as $folder)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border-t-4 dark-mode-transition" style="border-top-color: {{ $folder->color_code }};">
                    <div class="p-6">
                        <!-- Folder Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl" style="background-color: {{ $folder->color_code }};">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white truncate">
                                        {{ $folder->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-bookmark text-xs"></i> {{ $folder->resources_count }} {{ Str::plural('resource', $folder->resources_count) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        @if($folder->description)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ $folder->description }}</p>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 text-sm mb-4 italic">No description</p>
                        @endif
                        
                        <!-- Metadata -->
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <i class="far fa-calendar"></i> Created {{ $folder->created_at->diffForHumans() }}
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t dark:border-gray-700">
                            <a href="{{ route('folders.show', $folder->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </a>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('folders.edit', $folder->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this folder? All resources inside will also be deleted.');">
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
        
        <!-- Statistics Summary -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-chart-bar text-blue-600 dark:text-blue-400"></i> Summary
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $folders->count() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Folders</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $folders->sum('resources_count') }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Resources</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($folders->avg('resources_count'), 1) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Avg per Folder</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $folders->where('resources_count', 0)->count() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Empty Folders</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
