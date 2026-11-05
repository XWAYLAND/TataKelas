@extends('layouts.app')

@section('title', $photo->title)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('photos.index') }}" class="inline-flex items-center gap-2 font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to photos
            </a>
            <span class="text-base-content/25">/</span>
            <a href="{{ route('folders.show', $photo->folder_id) }}" class="inline-flex items-center gap-1.5 font-medium text-base-content/55 transition hover:text-primary">
                <span class="h-2 w-2 rounded-full" style="background-color: {{ $photo->folder->color_code }};"></span>
                {{ $photo->folder->name }}
            </a>
        </nav>

        <!-- Header -->
        <section class="app-surface overflow-hidden">
            <div class="folder-accent h-1.5" style="--folder-color: {{ $photo->folder->color_code }};"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $photo->title }}</h1>
                    <p class="mt-2 text-xs text-base-content/45">
                        <i class="far fa-calendar mr-1"></i> Uploaded {{ $photo->created_at->format('M d, Y') }}
                        <span class="mx-1.5">&middot;</span>
                        {{ $photo->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost border-base-300"><i class="fas fa-expand"></i> Full size</a>
                    <a href="{{ route('photos.edit', $photo->id) }}" class="btn btn-ghost border-base-300"><i class="fas fa-pen"></i> Edit</a>
                    <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Delete this photo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost border-base-300 text-error hover:bg-error/10"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Preview -->
            <section class="app-surface overflow-hidden p-3 lg:col-span-2">
                <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-xl bg-base-200">
                    <img src="{{ $photo->url }}" alt="{{ $photo->title }}" class="mx-auto max-h-[70vh] w-full object-contain">
                </a>
            </section>

            <!-- Details -->
            <div class="space-y-5">
                <section class="app-surface p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50"><i class="fas fa-circle-info mr-1 text-secondary"></i> File details</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-base-content/50">Original name</dt>
                            <dd class="break-all text-right font-medium">{{ $photo->filename }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-base-content/50">Type</dt>
                            <dd class="text-right font-medium">{{ $photo->mime_type ?: 'Unknown' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-base-content/50">Size</dt>
                            <dd class="text-right font-medium">{{ $photo->formatted_size }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="app-surface p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50">Folder</h2>
                    <a href="{{ route('folders.show', $photo->folder_id) }}" class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-white transition hover:opacity-80" style="background-color: {{ $photo->folder->color_code }};">
                        <i class="fas fa-folder text-xs"></i> {{ $photo->folder->name }}
                    </a>
                </section>

                @if($photo->description)
                    <section class="app-surface p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/50"><i class="fas fa-align-left mr-1 text-warning"></i> Description</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-base-content/65">{{ $photo->description }}</p>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection
