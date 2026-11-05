@extends('layouts.app')

@section('title', $folder->name)

@section('content')
<div class="mb-8">
    <!-- Breadcrumb -->
    <nav class="mb-4">
        <a href="{{ route('folders.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </nav>
    
    <!-- Folder Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 dark-mode-transition" style="border-left: 6px solid {{ $folder->color_code }};">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
            <div class="flex-1 mb-4 lg:mb-0">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-14 h-14 rounded-lg flex items-center justify-center text-white text-2xl" style="background-color: {{ $folder->color_code }};">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $folder->name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created {{ $folder->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                @if($folder->description)
                    <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $folder->description }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('folders.edit', $folder->id) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will delete all items in this folder.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400 px-5 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-center dark-mode-transition">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $itemCounts['total'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Items</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-center dark-mode-transition">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $itemCounts['resources'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Resources</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-center dark-mode-transition">
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $itemCounts['notes'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Notes</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-center dark-mode-transition">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $itemCounts['photos'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Photos</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-center dark-mode-transition">
            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['completed'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Completed</div>
        </div>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6 dark-mode-transition">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-1 px-6 overflow-x-auto" role="tablist">
                <button onclick="switchTab('resources')" id="tab-resources" class="tab-button whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 {{ $activeTab === 'resources' ? 'active border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    <i class="fas fa-bookmark mr-2"></i> Resources ({{ $itemCounts['resources'] }})
                </button>
                <button onclick="switchTab('notes')" id="tab-notes" class="tab-button whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 {{ $activeTab === 'notes' ? 'active border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    <i class="fas fa-sticky-note mr-2"></i> Notes ({{ $itemCounts['notes'] }})
                </button>
                <button onclick="switchTab('photos')" id="tab-photos" class="tab-button whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 {{ $activeTab === 'photos' ? 'active border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    <i class="fas fa-image mr-2"></i> Photos ({{ $itemCounts['photos'] }})
                </button>
            </nav>
        </div>
        
        <!-- Tab Content: Resources -->
        <div id="content-resources" class="folder-tab-panel {{ $activeTab === 'resources' ? '' : 'hidden' }}">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    <i class="fas fa-bookmark text-blue-600 dark:text-blue-400"></i> Resources
                </h2>
                <a href="{{ route('resources.create', ['folder_id' => $folder->id]) }}" class="bg-blue-600 dark:bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Resource
                </a>
            </div>
            
            @if($resources->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-bookmark text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Resources Yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Start adding resources to this folder</p>
                    <a href="{{ route('resources.create', ['folder_id' => $folder->id]) }}" class="bg-blue-600 dark:bg-blue-500 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition shadow-md inline-block">
                        <i class="fas fa-plus mr-2"></i> Add First Resource
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title & URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Added</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($resources as $resource)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                                    <a href="{{ route('resources.show', $resource->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $resource->title }}</a>
                                                </div>
                                                <a href="{{ $resource->url }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 break-all">
                                                    <i class="fas fa-external-link-alt mr-1"></i> {{ Str::limit($resource->url, 50) }}
                                                </a>
                                                @if($resource->notes)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                        <i class="fas fa-sticky-note"></i> {{ Str::limit($resource->notes, 100) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $resource->type_badge_color }}">
                                            {{ ucfirst($resource->resource_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('resources.update-status', $resource->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="read_status" onchange="this.form.submit()" class="text-xs font-semibold rounded-full px-3 py-1 border-0 cursor-pointer {{ $resource->status_badge_color }}">
                                                <option value="to_read" {{ $resource->read_status == 'to_read' ? 'selected' : '' }}>To Read</option>
                                                <option value="reading" {{ $resource->read_status == 'reading' ? 'selected' : '' }}>Reading</option>
                                                <option value="completed" {{ $resource->read_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $resource->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="{{ route('resources.edit', $resource->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($folders->count() > 0)
                                                <button onclick="showMoveModal({{ $resource->id}}, '{{ addslashes($resource->title) }}')" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300" title="Move">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300" title="Delete">
                                                    <i class="fas fa-trash"></i>
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
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $resources->appends(array_merge(request()->query(), ['tab' => 'resources']))->links() }}
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Tab Content: Notes -->
        <div id="content-notes" class="folder-tab-panel {{ $activeTab === 'notes' ? '' : 'hidden' }}">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    <i class="fas fa-sticky-note text-yellow-600 dark:text-yellow-400"></i> Notes
                </h2>
                <a href="{{ route('notes.create', ['folder_id' => $folder->id]) }}" class="bg-yellow-600 dark:bg-yellow-700 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Note
                </a>
            </div>
            
            @if($notes->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-sticky-note text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Notes Yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first note to organize thoughts and ideas</p>
                    <a href="{{ route('notes.create', ['folder_id' => $folder->id]) }}" class="bg-yellow-600 dark:bg-yellow-700 text-white px-6 py-2.5 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-600 transition shadow-md inline-block">
                        <i class="fas fa-plus mr-2"></i> Create First Note
                    </a>
                </div>
            @else
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($notes as $note)
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5 hover:shadow-md transition border-l-4 {{ $note->priority == 'high' ? 'border-red-500' : ($note->priority == 'medium' ? 'border-yellow-500' : 'border-green-500') }}">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <a href="{{ route('notes.show', $note->id) }}" class="hover:text-yellow-600 dark:hover:text-yellow-400">{{ $note->title }}</a>
                                </h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $note->priority_badge_color }}">
                                    <i class="fas {{ $note->priority_icon }}"></i> {{ ucfirst($note->priority) }}
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 whitespace-pre-line line-clamp-4">{{ $note->content }}</p>
                            <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                                <span><i class="far fa-calendar"></i> {{ $note->created_at->diffForHumans() }}</span>
                                <div class="flex space-x-2">
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
                        {{ $notes->appends(array_merge(request()->query(), ['tab' => 'notes']))->links() }}
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Tab Content: Photos -->
        <div id="content-photos" class="folder-tab-panel {{ $activeTab === 'photos' ? '' : 'hidden' }}">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    <i class="fas fa-image text-purple-600 dark:text-purple-400"></i> Photos
                </h2>
                <a href="{{ route('photos.create', ['folder_id' => $folder->id]) }}" class="bg-purple-600 dark:bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Upload Photo
                </a>
            </div>
            
            @if($photos->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-image text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Photos Yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Upload your first photo</p>
                    <a href="{{ route('photos.create', ['folder_id' => $folder->id]) }}" class="bg-purple-600 dark:bg-purple-500 text-white px-6 py-2.5 rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition shadow-md inline-block">
                        <i class="fas fa-upload mr-2"></i> Upload First Photo
                    </a>
                </div>
            @else
                <div class="p-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($photos as $photo)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                            <a href="{{ route('photos.show', $photo->id) }}" class="block aspect-square overflow-hidden bg-gray-100 dark:bg-gray-900">
                                <img src="{{ $photo->url }}" alt="{{ $photo->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                            </a>
                            <div class="p-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm truncate mb-1">
                                    <a href="{{ route('photos.show', $photo->id) }}" class="hover:text-purple-600 dark:hover:text-purple-400">{{ $photo->title }}</a>
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $photo->formatted_size }}</p>
                                <div class="flex justify-between items-center">
                                    <a href="{{ $photo->url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-xs" title="View Full Size">
                                        <i class="fas fa-expand-alt"></i>
                                    </a>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('photos.edit', $photo->id) }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 text-xs" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this photo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($photos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $photos->appends(array_merge(request()->query(), ['tab' => 'photos']))->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Move Resource Modal -->
@if($folders->count() > 0)
<div id="moveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 dark-mode-transition">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-exchange-alt text-purple-600"></i> Move Resource
        </h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Move "<span id="resourceTitle" class="font-semibold"></span>" to:</p>
        
        <form id="moveForm" method="POST">
            @csrf
            @method('PATCH')
            <select name="folder_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-6">
                @foreach($folders as $f)
                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                @endforeach
            </select>
            
            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="closeMoveModal()" class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition shadow-md">
                    <i class="fas fa-check mr-2"></i> Move
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.folder-tab-panel').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'border-blue-600', 'dark:border-blue-400', 'text-blue-600', 'dark:text-blue-400');
            button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        });
        
        // Show selected tab content
        document.getElementById('content-' + tabName).classList.remove('hidden');
        
        // Add active class to selected button
        const activeButton = document.getElementById('tab-' + tabName);
        activeButton.classList.add('active', 'border-blue-600', 'dark:border-blue-400', 'text-blue-600', 'dark:text-blue-400');
        activeButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');

        // Keep the selected tab in the URL so a refresh returns to it.
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    // Restore the tab selected by the controller.
    switchTab('{{ $activeTab }}');
    
    function showMoveModal(resourceId, resourceTitle) {
        document.getElementById('moveModal').classList.remove('hidden');
        document.getElementById('resourceTitle').textContent = resourceTitle;
        document.getElementById('moveForm').action = `/resources/${resourceId}/move`;
    }
    
    function closeMoveModal() {
        document.getElementById('moveModal').classList.add('hidden');
    }
    
    // Close modal on outside click
    document.getElementById('moveModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMoveModal();
        }
    });
</script>
@endpush
@endsection
