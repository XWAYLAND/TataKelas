@extends('layouts.app')

@section('title', $note->title)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('notes.index') }}" class="inline-flex items-center gap-2 font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to notes
            </a>
            <span class="text-base-content/25">/</span>
            <a href="{{ route('folders.show', $note->folder_id) }}" class="inline-flex items-center gap-1.5 font-medium text-base-content/55 transition hover:text-primary">
                <span class="h-2 w-2 rounded-full" style="background-color: {{ $note->folder->color_code }};"></span>
                {{ $note->folder->name }}
            </a>
        </nav>

        <!-- Header -->
        <section class="app-surface overflow-hidden">
            <div class="folder-accent h-1.5" style="--folder-color: {{ $note->folder->color_code }};"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <span class="badge border-0 {{ $note->priority_badge_color }}">
                        <i class="fas {{ $note->priority_icon }} text-[10px]"></i> {{ ucfirst($note->priority) }} priority
                    </span>

                    <h1 class="mt-3 break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $note->title }}</h1>

                    <p class="mt-3 text-xs text-base-content/45">
                        <i class="far fa-calendar mr-1"></i> Created {{ $note->created_at->format('M d, Y') }}
                        <span class="mx-1.5">&middot;</span>
                        {{ $note->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('notes.edit', $note->id) }}" class="btn btn-ghost border-base-300"><i class="fas fa-pen"></i> Edit</a>
                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Delete this note?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost border-base-300 text-error hover:bg-error/10"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Content -->
        <section class="app-surface p-5 sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50"><i class="fas fa-align-left mr-1 text-warning"></i> Note content</h2>
            <div class="mt-4 whitespace-pre-line text-sm leading-7 text-base-content/75">{{ $note->content }}</div>
        </section>

        <!-- Folder -->
        <section class="app-surface p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50">Folder</h2>
            <a href="{{ route('folders.show', $note->folder_id) }}" class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-white transition hover:opacity-80" style="background-color: {{ $note->folder->color_code }};">
                <i class="fas fa-folder text-xs"></i> {{ $note->folder->name }}
            </a>
        </section>
    </div>
@endsection
