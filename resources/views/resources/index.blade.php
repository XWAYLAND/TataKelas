@extends('layouts.app')

@section('title', 'All Resources')

@section('content')
    <div class="space-y-6">
        <!-- Page heading -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Library</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">All Resources</h1>
                <p class="mt-2 text-sm text-base-content/60">Browse, filter, and manage every link saved across your folders.</p>
            </div>
            <a href="{{ route('resources.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Resource
            </a>
        </div>

        <!-- Filters -->
        <section class="app-surface p-5">
            <form action="{{ route('resources.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="mb-2 block text-sm font-medium">Search</label>
                        <label class="input input-bordered flex w-full items-center gap-2">
                            <i class="fas fa-magnifying-glass text-xs text-base-content/40"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="grow" placeholder="Search by title, notes, or URL...">
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
                        <label for="resource_type" class="mb-2 block text-sm font-medium">Type</label>
                        <select name="resource_type" id="resource_type" class="select select-bordered w-full">
                            <option value="">All types</option>
                            <option value="documentation" {{ request('resource_type') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                            <option value="url" {{ request('resource_type') == 'url' ? 'selected' : '' }}>Link</option>
                            <option value="file" {{ request('resource_type') == 'file' ? 'selected' : '' }}>File</option>
                            <option value="video" {{ request('resource_type') == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="assignment_instruction" {{ request('resource_type') == 'assignment_instruction' ? 'selected' : '' }}>Assignment instructions</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="w-full sm:w-52">
                        <label for="read_status" class="mb-2 block text-sm font-medium">Reading status</label>
                        <select name="read_status" id="read_status" class="select select-bordered w-full">
                            <option value="">All statuses</option>
                            <option value="to_read" {{ request('read_status') == 'to_read' ? 'selected' : '' }}>To Read</option>
                            <option value="reading" {{ request('read_status') == 'reading' ? 'selected' : '' }}>Reading</option>
                            <option value="completed" {{ request('read_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply filters</button>
                        <a href="{{ route('resources.index') }}" class="btn btn-ghost border-base-300"><i class="fas fa-rotate-left"></i> Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- Results -->
        <section class="app-surface overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                <div>
                    <h2 class="text-lg font-semibold">Resources <span class="text-base-content/40">({{ $resources->total() }})</span></h2>
                    <p class="text-xs text-base-content/50">Sorted by the most recently added.</p>
                </div>
                @if(request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                    <span class="badge border-0 bg-primary/10 text-primary"><i class="fas fa-filter mr-1"></i> Filtered</span>
                @endif
            </div>

            @if($resources->isEmpty())
                <div class="flex flex-col items-center px-6 py-16 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-base-200 text-2xl text-base-content/30"><i class="fas fa-inbox"></i></span>
                    <h3 class="mt-4 font-semibold">No resources found</h3>
                    <p class="mt-1 max-w-sm text-sm text-base-content/55">
                        @if(request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                            Try adjusting your filters or search terms.
                        @else
                            Start adding resources to build your library.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'folder_id', 'resource_type', 'read_status']))
                        <a href="{{ route('resources.create') }}" class="btn btn-primary mt-5"><i class="fas fa-plus"></i> Add First Resource</a>
                    @endif
                </div>
            @else
                <div class="divide-y divide-base-300/70">
                    @foreach($resources as $resource)
                        <article class="p-5 transition hover:bg-base-200/30">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="badge border-0 {{ $resource->type_badge_color }}">{{ ucfirst($resource->resource_type) }}</span>
                                        <span class="badge border-0 {{ $resource->status_badge_color }}">{{ ucfirst(str_replace('_', ' ', $resource->read_status)) }}</span>
                                        <a href="{{ route('folders.show', $resource->folder->id) }}" class="badge border-0 text-white hover:opacity-80" style="background-color: {{ $resource->folder->color_code }};">
                                            <i class="fas fa-folder mr-1 text-[10px]"></i> {{ $resource->folder->name }}
                                        </a>
                                    </div>

                                    <h3 class="text-lg font-semibold leading-snug">
                                        <a href="{{ route('resources.show', $resource->id) }}" class="hover:text-primary">{{ $resource->title }}</a>
                                    </h3>

                                    @if($resource->url)
                                        <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block break-all text-sm text-primary hover:underline">
                                            <i class="fas fa-arrow-up-right-from-square mr-1 text-[10px]"></i> {{ Str::limit($resource->url, 90) }}
                                        </a>
                                    @elseif($resource->file_path)
                                        <a href="{{ route('resources.file', $resource) }}" class="mt-1 inline-flex items-center gap-2 text-sm text-primary hover:underline">
                                            <i class="fas fa-download text-[10px]"></i> Download attached material
                                        </a>
                                    @endif

                                    @if($resource->notes)
                                        <p class="mt-3 rounded-lg bg-base-200/60 px-3 py-2 text-sm text-base-content/60">
                                            <i class="fas fa-sticky-note mr-1 text-warning"></i> {{ Str::limit($resource->notes, 160) }}
                                        </p>
                                    @endif

                                    <p class="mt-3 text-xs text-base-content/45">
                                        <i class="far fa-calendar mr-1"></i> Added {{ $resource->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-1 lg:flex-col lg:items-end">
                                    <a href="{{ route('resources.show', $resource->id) }}" class="icon-action" title="View resource"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="{{ route('folders.show', $resource->folder_id) }}" class="icon-action" title="Open folder"><i class="fas fa-folder-open text-xs"></i></a>
                                    <a href="{{ route('resources.edit', $resource->id) }}" class="icon-action" title="Edit resource"><i class="fas fa-pen text-xs"></i></a>
                                    <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action is-danger" title="Delete resource"><i class="fas fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($resources->hasPages())
                    <div class="border-t border-base-300/70 px-5 py-4">
                        {{ $resources->appends(request()->query())->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
