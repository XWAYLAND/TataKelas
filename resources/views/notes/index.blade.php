@extends('layouts.app')

@section('title', 'All Notes')

@section('content')
    <div class="space-y-6">
        <!-- Page heading -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-warning">Notebook</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">All Notes</h1>
                <p class="mt-2 text-sm text-base-content/60">Capture ideas, summaries, and reminders from every folder.</p>
            </div>
            <a href="{{ route('notes.create') }}" class="btn btn-warning">
                <i class="fas fa-plus"></i> Create Note
            </a>
        </div>

        <!-- Filters -->
        <section class="app-surface p-5">
            <form action="{{ route('notes.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="mb-2 block text-sm font-medium">Search</label>
                        <label class="input input-bordered flex w-full items-center gap-2">
                            <i class="fas fa-magnifying-glass text-xs text-base-content/40"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="grow" placeholder="Search by title or content...">
                        </label>
                    </div>

                    <div>
                        <label for="folder_id" class="mb-2 block text-sm font-medium">Folder</label>
                        <select name="folder_id" id="folder_id" class="select select-bordered w-full">
                            <option value="">All folders</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="mb-2 block text-sm font-medium">Priority</label>
                        <select name="priority" id="priority" class="select select-bordered w-full">
                            <option value="">All priorities</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-filter"></i> Apply filters</button>
                    <a href="{{ route('notes.index') }}" class="btn btn-ghost border-base-300"><i class="fas fa-rotate-left"></i> Reset</a>
                </div>
            </form>
        </section>

        <!-- Results -->
        <section class="app-surface overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                <div>
                    <h2 class="text-lg font-semibold">Notes <span class="text-base-content/40">({{ $notes->total() }})</span></h2>
                    <p class="text-xs text-base-content/50">Sorted by the most recently created.</p>
                </div>
                @if(request()->hasAny(['search', 'folder_id', 'priority']))
                    <span class="badge border-0 bg-warning/15 text-warning"><i class="fas fa-filter mr-1"></i> Filtered</span>
                @endif
            </div>

            @if($notes->isEmpty())
                <div class="flex flex-col items-center px-6 py-16 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-base-200 text-2xl text-base-content/30"><i class="fas fa-sticky-note"></i></span>
                    <h3 class="mt-4 font-semibold">No notes found</h3>
                    <p class="mt-1 max-w-sm text-sm text-base-content/55">
                        @if(request()->hasAny(['search', 'folder_id', 'priority']))
                            Try adjusting your filters or search terms.
                        @else
                            Start creating notes to organize your thoughts and ideas.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'folder_id', 'priority']))
                        <a href="{{ route('notes.create') }}" class="btn btn-warning mt-5"><i class="fas fa-plus"></i> Create First Note</a>
                    @endif
                </div>
            @else
                <div class="grid gap-4 p-5 md:grid-cols-2">
                    @foreach($notes as $note)
                        <article class="flex flex-col rounded-xl border border-base-300/80 bg-base-200/35 p-5 transition hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold leading-snug">
                                        <a href="{{ route('notes.show', $note->id) }}" class="hover:text-warning">{{ $note->title }}</a>
                                    </h3>
                                    <a href="{{ route('folders.show', $note->folder_id) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-base-content/55 hover:text-primary">
                                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $note->folder->color_code }};"></span>
                                        {{ $note->folder->name }}
                                    </a>
                                </div>
                                <span class="badge shrink-0 border-0 {{ $note->priority_badge_color }}">
                                    <i class="fas {{ $note->priority_icon }} text-[10px]"></i> {{ ucfirst($note->priority) }}
                                </span>
                            </div>

                            <p class="mt-3 line-clamp-4 whitespace-pre-line text-sm leading-relaxed text-base-content/60">{{ $note->content }}</p>

                            <div class="mt-auto flex items-center justify-between gap-3 border-t border-base-300/70 pt-3 text-xs text-base-content/45">
                                <span><i class="far fa-calendar mr-1"></i> {{ $note->created_at->diffForHumans() }}</span>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('notes.show', $note->id) }}" class="icon-action" title="View note"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="{{ route('notes.edit', $note->id) }}" class="icon-action" title="Edit note"><i class="fas fa-pen text-xs"></i></a>
                                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Delete this note?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action is-danger" title="Delete note"><i class="fas fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($notes->hasPages())
                    <div class="border-t border-base-300/70 px-5 py-4">
                        {{ $notes->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
