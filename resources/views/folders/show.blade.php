@extends('layouts.app')

@section('title', $folder->name)

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav class="text-sm">
            <a href="{{ route('folders.index') }}" class="inline-flex items-center gap-2 font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to dashboard
            </a>
        </nav>

        <!-- Folder header -->
        <section class="app-surface overflow-hidden">
            <div class="folder-accent h-1.5" style="--folder-color: {{ $folder->color_code }};"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl text-2xl text-white shadow-sm" style="background-color: {{ $folder->color_code }};">
                        <i class="fas fa-folder"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-base-content/40">Folder workspace</p>
                        <h1 class="mt-1 break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $folder->name }}</h1>
                        <p class="mt-1 text-sm text-base-content/55">
                            <i class="far fa-calendar mr-1"></i> Created {{ $folder->created_at->format('M d, Y') }}
                        </p>
                        @if($folder->description)
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-base-content/65">{{ $folder->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('folders.edit', $folder->id) }}" class="btn btn-ghost border-base-300">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will delete all items in this folder.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost border-base-300 text-error hover:bg-error/10">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            <div class="app-surface flex items-center gap-3 p-4">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary"><i class="fas fa-layer-group"></i></span>
                <div><p class="text-xl font-bold leading-none">{{ $itemCounts['total'] }}</p><p class="mt-1 text-xs text-base-content/55">Total items</p></div>
            </div>
            <div class="app-surface flex items-center gap-3 p-4">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-success/10 text-success"><i class="fas fa-bookmark"></i></span>
                <div><p class="text-xl font-bold leading-none">{{ $itemCounts['resources'] }}</p><p class="mt-1 text-xs text-base-content/55">Resources</p></div>
            </div>
            <div class="app-surface flex items-center gap-3 p-4">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-warning/10 text-warning"><i class="fas fa-sticky-note"></i></span>
                <div><p class="text-xl font-bold leading-none">{{ $itemCounts['notes'] }}</p><p class="mt-1 text-xs text-base-content/55">Notes</p></div>
            </div>
            <div class="app-surface flex items-center gap-3 p-4">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-secondary/10 text-secondary"><i class="fas fa-image"></i></span>
                <div><p class="text-xl font-bold leading-none">{{ $itemCounts['photos'] }}</p><p class="mt-1 text-xs text-base-content/55">Photos</p></div>
            </div>
            <div class="app-surface col-span-2 flex items-center gap-3 p-4 sm:col-span-1">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-info/10 text-info"><i class="fas fa-circle-check"></i></span>
                <div><p class="text-xl font-bold leading-none">{{ $stats['completed'] }}</p><p class="mt-1 text-xs text-base-content/55">Completed</p></div>
            </div>
        </div>

        <!-- Tabs -->
        <section class="app-surface overflow-hidden">
            <div class="border-b border-base-300/70 p-3">
                <nav class="flex gap-1 overflow-x-auto" role="tablist" aria-label="Folder contents">
                    <button type="button" onclick="switchTab('resources')" id="tab-resources" role="tab" class="tab-button tab-pill {{ $activeTab === 'resources' ? 'tab-pill-active' : '' }}">
                        <i class="fas fa-bookmark"></i> Resources
                        <span class="badge badge-sm border-0 bg-base-200 text-base-content/60">{{ $itemCounts['resources'] }}</span>
                    </button>
                    <button type="button" onclick="switchTab('notes')" id="tab-notes" role="tab" class="tab-button tab-pill {{ $activeTab === 'notes' ? 'tab-pill-active' : '' }}">
                        <i class="fas fa-sticky-note"></i> Notes
                        <span class="badge badge-sm border-0 bg-base-200 text-base-content/60">{{ $itemCounts['notes'] }}</span>
                    </button>
                    <button type="button" onclick="switchTab('photos')" id="tab-photos" role="tab" class="tab-button tab-pill {{ $activeTab === 'photos' ? 'tab-pill-active' : '' }}">
                        <i class="fas fa-image"></i> Photos
                        <span class="badge badge-sm border-0 bg-base-200 text-base-content/60">{{ $itemCounts['photos'] }}</span>
                    </button>
                </nav>
            </div>

            <!-- Resources -->
            <div id="content-resources" class="folder-tab-panel {{ $activeTab === 'resources' ? '' : 'hidden' }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                    <div>
                        <h2 class="text-lg font-semibold"><i class="fas fa-bookmark mr-1 text-primary"></i> Resources</h2>
                        <p class="text-xs text-base-content/50">Links, docs, and references saved in this folder.</p>
                    </div>
                    <a href="{{ route('resources.create', ['folder_id' => $folder->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Resource
                    </a>
                </div>

                @if($resources->isEmpty())
                    <div class="flex flex-col items-center px-6 py-14 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-primary/10 text-2xl text-primary"><i class="fas fa-bookmark"></i></span>
                        <h3 class="mt-4 font-semibold">No resources yet</h3>
                        <p class="mt-1 max-w-sm text-sm text-base-content/55">Start adding links, articles, and documentation to this folder.</p>
                        <a href="{{ route('resources.create', ['folder_id' => $folder->id]) }}" class="btn btn-primary mt-5">
                            <i class="fas fa-plus"></i> Add First Resource
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-sm min-w-[760px]">
                            <thead class="bg-base-200/60 text-xs uppercase tracking-wide text-base-content/50">
                                <tr>
                                    <th class="font-semibold">Title &amp; URL</th>
                                    <th class="font-semibold">Type</th>
                                    <th class="font-semibold">Status</th>
                                    <th class="font-semibold">Added</th>
                                    <th class="text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resources as $resource)
                                    <tr class="hover:bg-base-200/40">
                                        <td>
                                            <div class="max-w-md">
                                                <a href="{{ route('resources.show', $resource->id) }}" class="font-semibold hover:text-primary">{{ $resource->title }}</a>
                                                <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 block truncate text-xs text-primary/80 hover:text-primary">
                                                    <i class="fas fa-arrow-up-right-from-square mr-1"></i> {{ Str::limit($resource->url, 60) }}
                                                </a>
                                                @if($resource->notes)
                                                    <p class="mt-1.5 line-clamp-1 text-xs text-base-content/50">
                                                        <i class="fas fa-sticky-note mr-1 text-warning"></i> {{ Str::limit($resource->notes, 100) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge border-0 {{ $resource->type_badge_color }}">{{ ucfirst($resource->resource_type) }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('resources.update-status', $resource->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="read_status" onchange="this.form.submit()" class="select select-xs w-32 rounded-full border-0 font-semibold {{ $resource->status_badge_color }}">
                                                    <option value="to_read" {{ $resource->read_status == 'to_read' ? 'selected' : '' }}>To Read</option>
                                                    <option value="reading" {{ $resource->read_status == 'reading' ? 'selected' : '' }}>Reading</option>
                                                    <option value="completed" {{ $resource->read_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="whitespace-nowrap text-xs text-base-content/50">{{ $resource->created_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1">
                                                @if($resource->files->isNotEmpty())
                                                    @if($resource->files->contains('is_previewable'))
                                                        <a href="{{ route('resources.files.preview', [$resource, $resource->files->firstWhere('is_previewable')]) }}" target="_blank" rel="noopener" class="icon-action" title="Preview material">
                                                            <i class="fas fa-eye text-xs"></i>
                                                        </a>
                                                    @endif
                                                    @if($resource->files->count() === 1)
                                                        <a href="{{ route('resources.files.download', [$resource, $resource->files->first()]) }}" class="icon-action" title="Download material">
                                                            <i class="fas fa-download text-xs"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('resources.show', $resource->id) }}" class="icon-action" title="Download one of {{ $resource->files->count() }} files">
                                                            <i class="fas fa-download text-xs"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                <a href="{{ route('resources.edit', $resource->id) }}" class="icon-action" title="Edit resource">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </a>
                                                @if($folders->count() > 0)
                                                    <button type="button"
                                                            data-move-resource="{{ $resource->id }}"
                                                            data-resource-title="{{ $resource->title }}"
                                                            onclick="showMoveModal(this)"
                                                            class="icon-action hover:text-secondary"
                                                            title="Move resource">
                                                        <i class="fas fa-arrow-right-arrow-left text-xs"></i>
                                                    </button>
                                                @endif
                                                <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-action is-danger" title="Delete resource">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($resources->hasPages())
                        <div class="border-t border-base-300/70 px-5 py-4">
                            {{ $resources->appends(array_merge(request()->query(), ['tab' => 'resources']))->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <!-- Notes -->
            <div id="content-notes" class="folder-tab-panel {{ $activeTab === 'notes' ? '' : 'hidden' }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                    <div>
                        <h2 class="text-lg font-semibold"><i class="fas fa-sticky-note mr-1 text-warning"></i> Notes</h2>
                        <p class="text-xs text-base-content/50">Thoughts, summaries, and ideas captured in this folder.</p>
                    </div>
                    <a href="{{ route('notes.create', ['folder_id' => $folder->id]) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus"></i> Add Note
                    </a>
                </div>

                @if($notes->isEmpty())
                    <div class="flex flex-col items-center px-6 py-14 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-warning/10 text-2xl text-warning"><i class="fas fa-sticky-note"></i></span>
                        <h3 class="mt-4 font-semibold">No notes yet</h3>
                        <p class="mt-1 max-w-sm text-sm text-base-content/55">Create your first note to keep important thoughts close to your resources.</p>
                        <a href="{{ route('notes.create', ['folder_id' => $folder->id]) }}" class="btn btn-warning mt-5">
                            <i class="fas fa-plus"></i> Create First Note
                        </a>
                    </div>
                @else
                    <div class="grid gap-4 p-5 md:grid-cols-2">
                        @foreach($notes as $note)
                            <article class="flex flex-col rounded-xl border border-base-300/80 bg-base-200/40 p-5 transition hover:shadow-md">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold leading-snug">
                                        <a href="{{ route('notes.show', $note->id) }}" class="hover:text-warning">{{ $note->title }}</a>
                                    </h3>
                                    <span class="badge shrink-0 border-0 {{ $note->priority_badge_color }}">
                                        <i class="fas {{ $note->priority_icon }} text-[10px]"></i> {{ ucfirst($note->priority) }}
                                    </span>
                                </div>
                                <p class="mt-3 line-clamp-4 whitespace-pre-line text-sm leading-relaxed text-base-content/60">{{ $note->content }}</p>
                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-base-300/70 pt-3 text-xs text-base-content/45">
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
                            {{ $notes->appends(array_merge(request()->query(), ['tab' => 'notes']))->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <!-- Photos -->
            <div id="content-photos" class="folder-tab-panel {{ $activeTab === 'photos' ? '' : 'hidden' }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                    <div>
                        <h2 class="text-lg font-semibold"><i class="fas fa-image mr-1 text-secondary"></i> Photos</h2>
                        <p class="text-xs text-base-content/50">Screenshots and images uploaded to this folder.</p>
                    </div>
                    <a href="{{ route('photos.create', ['folder_id' => $folder->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-upload"></i> Upload Photo
                    </a>
                </div>

                @if($photos->isEmpty())
                    <div class="flex flex-col items-center px-6 py-14 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-secondary/10 text-2xl text-secondary"><i class="fas fa-image"></i></span>
                        <h3 class="mt-4 font-semibold">No photos yet</h3>
                        <p class="mt-1 max-w-sm text-sm text-base-content/55">Upload a screenshot, diagram, or reference image to this folder.</p>
                        <a href="{{ route('photos.create', ['folder_id' => $folder->id]) }}" class="btn btn-secondary mt-5">
                            <i class="fas fa-upload"></i> Upload First Photo
                        </a>
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
                                    <p class="mt-1 text-xs text-base-content/45">{{ $photo->formatted_size }}</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer" class="icon-action" title="View full size"><i class="fas fa-expand text-xs"></i></a>
                                        <div class="flex items-center gap-1">
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
                            {{ $photos->appends(array_merge(request()->query(), ['tab' => 'photos']))->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </div>

    <!-- Move resource modal -->
    @if($folders->count() > 0)
        <div id="moveModal" class="modal">
            <div class="modal-box max-w-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold"><i class="fas fa-arrow-right-arrow-left mr-1 text-secondary"></i> Move Resource</h3>
                        <p class="mt-1 text-sm text-base-content/55">Move "<span id="resourceTitle" class="font-semibold text-base-content"></span>" to another folder.</p>
                    </div>
                    <button type="button" onclick="closeMoveModal()" class="btn btn-ghost btn-sm btn-circle" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <form id="moveForm" method="POST" class="mt-5">
                    @csrf
                    @method('PATCH')
                    <label for="move-folder" class="mb-2 block text-sm font-medium">Destination folder</label>
                    <select id="move-folder" name="folder_id" class="select select-bordered w-full">
                        @foreach($folders as $destination)
                            <option value="{{ $destination->id }}">{{ $destination->name }}</option>
                        @endforeach
                    </select>

                    <div class="modal-action">
                        <button type="button" onclick="closeMoveModal()" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-check"></i> Move</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.folder-tab-panel').forEach(function (content) {
                content.classList.add('hidden');
            });

            document.querySelectorAll('.tab-button').forEach(function (button) {
                button.classList.remove('tab-pill-active');
            });

            document.getElementById('content-' + tabName)?.classList.remove('hidden');
            document.getElementById('tab-' + tabName)?.classList.add('tab-pill-active');

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        }

        switchTab(@js($activeTab));

        function showMoveModal(button) {
            const modal = document.getElementById('moveModal');
            if (!modal) return;

            document.getElementById('resourceTitle').textContent = button.dataset.resourceTitle;
            document.getElementById('moveForm').action = `/resources/${button.dataset.moveResource}/move`;
            modal.classList.add('modal-open');
        }

        function closeMoveModal() {
            document.getElementById('moveModal')?.classList.remove('modal-open');
        }

        document.getElementById('moveModal')?.addEventListener('click', function (event) {
            if (event.target === this) closeMoveModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMoveModal();
        });
    </script>
@endpush
