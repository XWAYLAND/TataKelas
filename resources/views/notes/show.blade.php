@extends('layouts.app')

@section('title', $note->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <a href="{{ route('notes.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Back to Notes
        </a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('folders.show', $note->folder_id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-folder mr-1"></i> {{ $note->folder->name }}
        </a>
    </nav>

    <!-- Note Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition" style="border-left: 6px solid {{ $note->folder->color_code }};">
        <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="mb-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $note->priority_badge_color }}">
                        <i class="fas {{ $note->priority_icon }}"></i> {{ ucfirst($note->priority) }} Priority
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2 break-words">{{ $note->title }}</h1>

                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="far fa-calendar mr-1"></i> Created {{ $note->created_at->format('M d, Y') }}</span>
                    <span class="mx-2">&middot;</span>
                    <span>{{ $note->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('notes.edit', $note->id) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this note?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400 px-4 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Note Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-align-left text-yellow-600 dark:text-yellow-400 mr-2"></i> Content
        </h2>
        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $note->content }}</div>
    </div>

    <!-- Folder -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 dark-mode-transition">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-folder text-blue-500 dark:text-blue-400 mr-2"></i> Folder
        </h2>
        <a href="{{ route('folders.show', $note->folder_id) }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-white hover:opacity-80 transition" style="background-color: {{ $note->folder->color_code }};">
            {{ $note->folder->name }}
        </a>
    </div>
</div>
@endsection
