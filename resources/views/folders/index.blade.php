@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <!-- Page heading -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Overview</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">My Folders</h1>
                <p class="mt-2 max-w-2xl text-sm text-base-content/60">
                    Organize your learning resources into focused workspaces. Every folder keeps its links, notes, and photos together.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('resources.create') }}" class="btn btn-ghost border-base-300">
                    <i class="fas fa-link"></i> Add Resource
                </a>
                <a href="{{ route('folders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Folder
                </a>
            </div>
        </div>

        @if($folders->isEmpty())
            <!-- Empty state -->
            <div class="app-surface flex flex-col items-center justify-center border-dashed px-6 py-16 text-center">
                <div class="grid h-20 w-20 place-items-center rounded-2xl bg-primary/10 text-3xl text-primary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="mt-5 text-xl font-semibold">No folders yet</h3>
                <p class="mt-2 max-w-md text-sm text-base-content/60">
                    Create your first folder to start collecting resources, notes, and photos in one place.
                </p>
                <a href="{{ route('folders.create') }}" class="btn btn-primary mt-6">
                    <i class="fas fa-plus"></i> Create First Folder
                </a>
            </div>
        @else
            <!-- Summary -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="app-surface flex items-center gap-3 p-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                        <i class="fas fa-folder"></i>
                    </span>
                    <div>
                        <p class="text-2xl font-bold leading-none">{{ $folders->count() }}</p>
                        <p class="mt-1 text-xs text-base-content/55">Total folders</p>
                    </div>
                </div>
                <div class="app-surface flex items-center gap-3 p-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-success/10 text-success">
                        <i class="fas fa-bookmark"></i>
                    </span>
                    <div>
                        <p class="text-2xl font-bold leading-none">{{ $folders->sum('resources_count') }}</p>
                        <p class="mt-1 text-xs text-base-content/55">Total resources</p>
                    </div>
                </div>
                <div class="app-surface flex items-center gap-3 p-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-secondary/10 text-secondary">
                        <i class="fas fa-chart-simple"></i>
                    </span>
                    <div>
                        <p class="text-2xl font-bold leading-none">{{ number_format($folders->avg('resources_count'), 1) }}</p>
                        <p class="mt-1 text-xs text-base-content/55">Avg per folder</p>
                    </div>
                </div>
                <div class="app-surface flex items-center gap-3 p-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-warning/10 text-warning">
                        <i class="fas fa-box-open"></i>
                    </span>
                    <div>
                        <p class="text-2xl font-bold leading-none">{{ $folders->where('resources_count', 0)->count() }}</p>
                        <p class="mt-1 text-xs text-base-content/55">Empty folders</p>
                    </div>
                </div>
            </div>

            <!-- Folder grid -->
            <div>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Your folders</h2>
                        <p class="text-xs text-base-content/50">{{ $folders->count() }} {{ Str::plural('workspace', $folders->count()) }} ready to browse</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($folders as $folder)
                        <article class="group app-surface flex flex-col overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="folder-accent h-1.5" style="--folder-color: {{ $folder->color_code }};"></div>

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg text-white shadow-sm" style="background-color: {{ $folder->color_code }};">
                                            <i class="fas fa-folder"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="truncate font-semibold">
                                                <a href="{{ route('folders.show', $folder->id) }}" class="hover:text-primary">{{ $folder->name }}</a>
                                            </h3>
                                            <p class="mt-0.5 text-xs text-base-content/50">
                                                <i class="fas fa-bookmark mr-1"></i>
                                                {{ $folder->resources_count }} {{ Str::plural('resource', $folder->resources_count) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="dropdown dropdown-end">
                                        <button type="button" tabindex="0" class="btn btn-ghost btn-xs btn-square" aria-label="Folder actions">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul tabindex="0" class="menu dropdown-content z-30 mt-1 w-44 rounded-box border border-base-300 bg-base-100 p-1.5 shadow-xl">
                                            <li><a href="{{ route('folders.show', $folder->id) }}"><i class="fas fa-eye w-4"></i> View</a></li>
                                            <li><a href="{{ route('folders.edit', $folder->id) }}"><i class="fas fa-pen w-4"></i> Edit</a></li>
                                            <li>
                                                <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder? All resources inside will also be deleted.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-error">
                                                        <i class="fas fa-trash w-4"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <p class="mt-4 line-clamp-2 text-sm leading-relaxed text-base-content/60">
                                    @if($folder->description)
                                        {{ $folder->description }}
                                    @else
                                        <span class="italic text-base-content/40">No description yet.</span>
                                    @endif
                                </p>

                                <p class="mt-4 text-xs text-base-content/45">
                                    <i class="far fa-calendar mr-1"></i> Created {{ $folder->created_at->diffForHumans() }}
                                </p>

                                <div class="mt-5 flex items-center justify-between gap-2 border-t border-base-300/70 pt-4">
                                    <a href="{{ route('folders.show', $folder->id) }}" class="btn btn-primary btn-sm">
                                        View folder <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('folders.edit', $folder->id) }}" class="icon-action" title="Edit folder">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder? All resources inside will also be deleted.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-action is-danger" title="Delete folder">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
