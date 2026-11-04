@extends('layouts.app')

@section('title', 'All Notes')

@section('content')
<div class="mb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                <i class="fas fa-sticky-note text-yellow-600 dark:text-yellow-400"></i> All Notes
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and filter all your notes</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('notes.create') }}" class="bg-yellow-600 dark:bg-yellow-700 text-white px-6 py-3 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition shadow-md inline-block">
                <i class="fas fa-plus mr-2"></i> Create Note
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition">
        <form action="{{ route('notes.index') }}" method="GET" class="space-y-4">
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
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                        placeholder="Search by title or content..."
                    >
                </div>

                <div>
                    <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-folder"></i> Folder
                    </label>
                    <select name="folder_id" id="folder_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <option value="">All Folders</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-flag"></i> Priority
                    </label>
                    <select name="priority" id="priority" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <option value="">All Priorities</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
            </div>

            <div class="flex items-end space-x-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 dark:bg-yellow-700 text-white rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition shadow-md">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                <a href="{{ route('notes.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden dark-mode-transition">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Notes ({{ $notes->total() }})
            </h2>
            @if(request()->hasAny(['search', 'folder_id', 'priority']))
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-filter text-yellow-600 dark:text-yellow-400"></i> Filtered
                </span>
            @endif
        </div>

        @if($notes->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-sticky-note text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Notes Found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if(request()->hasAny(['search', 'folder_id', 'priority']))
                        Try adjusting your filters or search terms
                    @else
                        Start creating notes to organize your thoughts and ideas
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'folder_id', 'priority']))
                    <a href="{{ route('notes.create') }}" class="bg-yellow-600 dark:bg-yellow-700 text-white px-6 py-2.5 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition shadow-md inline-block">
                        <i class="fas fa-plus mr-2"></i> Create First Note
                    </a>
                @endif
            </div>
        @else
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($notes as $note)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5 hover:shadow-md transition border-l-4 {{ $note->priority === 'high' ? 'border-red-500' : ($note->priority === 'medium' ? 'border-yellow-500' : 'border-green-500') }}">
                        <div class="flex justify-between items-start mb-3 gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <a href="{{ route('notes.show', $note->id) }}" class="hover:text-yellow-600 dark:hover:text-yellow-400">{{ $note->title }}</a>
                            </h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap {{ $note->priority_badge_color }}">
                                <i class="fas {{ $note->priority_icon }}"></i> {{ ucfirst($note->priority) }}
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 whitespace-pre-line line-clamp-4">{{ $note->content }}</p>

                        <div class="flex flex-wrap justify-between items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('folders.show', $note->folder_id) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium text-white hover:opacity-80 transition" style="background-color: {{ $note->folder->color_code }};">
                                    <i class="fas fa-folder mr-1"></i> {{ $note->folder->name }}
                                </a>
                                <span><i class="far fa-calendar"></i> {{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('notes.show', $note->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('notes.edit', $note->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this note?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $notes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
