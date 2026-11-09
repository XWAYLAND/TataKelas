@extends('layouts.app')

@section('title', $resource->title)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('resources.index') }}" class="inline-flex items-center gap-2 font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to resources
            </a>
            <span class="text-base-content/25">/</span>
            <a href="{{ route('folders.show', $resource->folder_id) }}" class="inline-flex items-center gap-1.5 font-medium text-base-content/55 transition hover:text-primary">
                <span class="h-2 w-2 rounded-full" style="background-color: {{ $resource->folder->color_code }};"></span>
                {{ $resource->folder->name }}
            </a>
        </nav>

        <!-- Header -->
        <section class="app-surface overflow-hidden">
            <div class="folder-accent h-1.5" style="--folder-color: {{ $resource->folder->color_code }};"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge border-0 {{ $resource->type_badge_color }}">{{ ucfirst($resource->resource_type) }}</span>
                        <span class="badge border-0 {{ $resource->status_badge_color }}">{{ ucfirst(str_replace('_', ' ', $resource->read_status)) }}</span>
                    </div>

                    <h1 class="mt-3 break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $resource->title }}</h1>

                    @if($resource->url)
                        <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block break-all text-sm text-primary hover:underline">
                            <i class="fas fa-arrow-up-right-from-square mr-1 text-[10px]"></i> {{ $resource->url }}
                        </a>
                    @endif

                    <p class="mt-4 text-xs text-base-content/45">
                        <i class="far fa-calendar mr-1"></i> Added {{ $resource->created_at->format('M d, Y') }}
                        <span class="mx-1.5">&middot;</span>
                        {{ $resource->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-ghost border-base-300"><i class="fas fa-pen"></i> Edit</a>
                    <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost border-base-300 text-error hover:bg-error/10"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Notes -->
            <section class="app-surface p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold"><i class="fas fa-sticky-note mr-1 text-warning"></i> Notes</h2>
                @if($resource->notes)
                    <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-base-content/70">{{ $resource->notes }}</p>
                @else
                    <p class="mt-4 text-sm italic text-base-content/40">No notes added for this resource.</p>
                @endif
            </section>

            <!-- Details -->
            <div class="space-y-5">
                <section class="app-surface p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50">Folder</h2>
                    <a href="{{ route('folders.show', $resource->folder_id) }}" class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-white transition hover:opacity-80" style="background-color: {{ $resource->folder->color_code }};">
                        <i class="fas fa-folder text-xs"></i> {{ $resource->folder->name }}
                    </a>
                </section>

                <section class="app-surface p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50">Reading status</h2>
                    <form action="{{ route('resources.update-status', $resource->id) }}" method="POST" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <select name="read_status" onchange="this.form.submit()" class="select select-bordered w-full cursor-pointer">
                            <option value="to_read" {{ $resource->read_status === 'to_read' ? 'selected' : '' }}>To Read</option>
                            <option value="reading" {{ $resource->read_status === 'reading' ? 'selected' : '' }}>Reading</option>
                            <option value="completed" {{ $resource->read_status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </form>
                </section>

                @if($resource->files->isNotEmpty())
                    <section class="app-surface p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50">Attached materials ({{ $resource->files->count() }})</h2>
                        <ul class="mt-3 space-y-3">
                            @foreach($resource->files as $file)
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-base-300/70 px-3 py-2">
                                    <span class="min-w-0 flex-1 truncate text-sm" title="{{ $file->original_name }}">
                                        <i class="far fa-file-pdf mr-1 text-xs text-base-content/55"></i> {{ $file->original_name }}
                                        <span class="ml-1 text-xs text-base-content/45">{{ number_format($file->size / 1024, 0) }} KB</span>
                                    </span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        @if($file->is_previewable)
                                            <a href="{{ route('resources.files.preview', [$resource, $file]) }}" target="_blank" rel="noopener" class="icon-action" title="Preview file"><i class="fas fa-eye text-xs"></i></a>
                                        @endif
                                        <a href="{{ route('resources.files.download', [$resource, $file]) }}" class="icon-action" title="Download file"><i class="fas fa-download text-xs"></i></a>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        @unless($resource->files->contains('is_previewable'))
                            <p class="mt-2 text-xs text-base-content/45">Preview is available for PDF files. Download a document to open it.</p>
                        @endunless
                    </section>
                @elseif($resource->url)
                    <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-full">
                        <i class="fas fa-arrow-up-right-from-square"></i> Open Resource
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
