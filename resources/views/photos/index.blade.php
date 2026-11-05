@extends('layouts.app')

@section('title', 'All Photos')

@section('content')
    <div class="space-y-6">
        <!-- Page heading -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-secondary">Gallery</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">All Photos</h1>
                <p class="mt-2 text-sm text-base-content/60">Every screenshot, diagram, and reference image you have uploaded.</p>
            </div>
            <a href="{{ route('photos.create') }}" class="btn btn-secondary">
                <i class="fas fa-upload"></i> Upload Photo
            </a>
        </div>

        <!-- Filters -->
        <section class="app-surface p-5">
            <form action="{{ route('photos.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="mb-2 block text-sm font-medium">Search</label>
                        <label class="input input-bordered flex w-full items-center gap-2">
                            <i class="fas fa-magnifying-glass text-xs text-base-content/40"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="grow" placeholder="Search by title or description...">
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

                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-1"><i class="fas fa-filter"></i> Apply</button>
                        <a href="{{ route('photos.index') }}" class="btn btn-ghost border-base-300" title="Reset filters"><i class="fas fa-rotate-left"></i></a>
                    </div>
                </div>
            </form>
        </section>

        <!-- Results -->
        <section class="app-surface overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                <div>
                    <h2 class="text-lg font-semibold">Photos <span class="text-base-content/40">({{ $photos->total() }})</span></h2>
                    <p class="text-xs text-base-content/50">Sorted by the most recently uploaded.</p>
                </div>
                @if(request()->hasAny(['search', 'folder_id']))
                    <span class="badge border-0 bg-secondary/15 text-secondary"><i class="fas fa-filter mr-1"></i> Filtered</span>
                @endif
            </div>

            @if($photos->isEmpty())
                <div class="flex flex-col items-center px-6 py-16 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-base-200 text-2xl text-base-content/30"><i class="fas fa-image"></i></span>
                    <h3 class="mt-4 font-semibold">No photos found</h3>
                    <p class="mt-1 max-w-sm text-sm text-base-content/55">
                        @if(request()->hasAny(['search', 'folder_id']))
                            Try adjusting your filters or search terms.
                        @else
                            Start uploading photos to keep them organized.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'folder_id']))
                        <a href="{{ route('photos.create') }}" class="btn btn-secondary mt-5"><i class="fas fa-upload"></i> Upload First Photo</a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 p-5 md:grid-cols-3 xl:grid-cols-4">
                    @foreach($photos as $photo)
                        <article class="group overflow-hidden rounded-xl border border-base-300/80 bg-base-100 transition hover:shadow-md">
                            <a href="{{ route('photos.show', $photo->id) }}" class="block aspect-square overflow-hidden bg-base-200">
                                <img src="{{ $photo->url }}" alt="{{ $photo->title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            </a>
                            <div class="p-3">
                                <h4 class="truncate text-sm font-semibold">
                                    <a href="{{ route('photos.show', $photo->id) }}" class="hover:text-secondary">{{ $photo->title }}</a>
                                </h4>
                                <a href="{{ route('folders.show', $photo->folder_id) }}" class="mt-1.5 inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-primary">
                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $photo->folder->color_code }};"></span>
                                    {{ $photo->folder->name }}
                                </a>
                                <p class="mt-1 text-xs text-base-content/40">{{ $photo->formatted_size }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer" class="icon-action" title="View full size"><i class="fas fa-expand text-xs"></i></a>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('photos.show', $photo->id) }}" class="icon-action" title="View photo"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('photos.edit', $photo->id) }}" class="icon-action" title="Edit photo"><i class="fas fa-pen text-xs"></i></a>
                                        <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Delete this photo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-action is-danger" title="Delete photo"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($photos->hasPages())
                    <div class="border-t border-base-300/70 px-5 py-4">
                        {{ $photos->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
