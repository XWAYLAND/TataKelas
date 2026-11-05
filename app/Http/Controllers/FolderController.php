<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    /**
     * Display a listing of folders (Dashboard).
     */
    public function index(): View
    {
        $folders = Folder::withCount('resources')
            ->whereNull('classroom_id')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('folders.index', compact('folders'));
    }

    /**
     * Show the form for creating a new folder.
     */
    public function create(): View
    {
        $this->authorize('create', Folder::class);

        return view('folders.create');
    }

    /**
     * Store a newly created folder in storage.
     */
    public function store(StoreFolderRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Set default color if not provided
            if (empty($validatedData['color_code'])) {
                $validatedData['color_code'] = '#3B82F6';
            }

            $folder = Folder::create($validatedData);

            return redirect()
                ->route('folders.index')
                ->with('success', "Folder '{$folder->name}' has been created successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create folder. Please try again.');
        }
    }

    /**
     * Display the specified folder with its resources.
     */
    public function show(Request $request, string $id): View|RedirectResponse
    {
        try {
            // All counts are produced by scalar subqueries, so the folder page
            // no longer loads every child row just to calculate statistics.
            $folder = Folder::withCount([
                'resources',
                'resources as to_read_resources_count' => fn ($query) => $query->where('read_status', 'to_read'),
                'resources as reading_resources_count' => fn ($query) => $query->where('read_status', 'reading'),
                'resources as completed_resources_count' => fn ($query) => $query->where('read_status', 'completed'),
                'notes',
                'photos',
            ])->findOrFail($id);
            $this->authorize('view', $folder);

            if ($folder->classroom_id) {
                return redirect()->route('classrooms.folders.show', [$folder->classroom_id, $folder->id]);
            }

            // Only the active tab's rows are loaded, 15/12 at a time.
            $resources = $folder->resources()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'resources_page')
                ->withQueryString();

            $notes = $folder->notes()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'notes_page')
                ->withQueryString();

            $photos = $folder->photos()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'photos_page')
                ->withQueryString();

            // Get all folders for the move-resource dropdown.
            $folders = Folder::select('id', 'name')
                ->whereNull('classroom_id')
                ->where('user_id', auth()->id())
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get();

            $stats = [
                'total' => $folder->resources_count,
                'to_read' => $folder->to_read_resources_count,
                'reading' => $folder->reading_resources_count,
                'completed' => $folder->completed_resources_count,
            ];

            $itemCounts = [
                'resources' => $folder->resources_count,
                'notes' => $folder->notes_count,
                'photos' => $folder->photos_count,
                'total' => $folder->resources_count + $folder->notes_count + $folder->photos_count,
            ];

            $activeTab = in_array($request->query('tab'), ['resources', 'notes', 'photos'], true)
                ? $request->query('tab')
                : ($request->has('notes_page') ? 'notes' : ($request->has('photos_page') ? 'photos' : 'resources'));

            return view('folders.show', compact(
                'folder',
                'folders',
                'stats',
                'itemCounts',
                'resources',
                'notes',
                'photos',
                'activeTab'
            ));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('folders.index')
                ->with('error', 'Folder not found.');
        }
    }

    /**
     * Show the form for editing the specified folder.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $folder = Folder::findOrFail($id);
            $this->authorize('update', $folder);

            return view('folders.edit', compact('folder'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('folders.index')
                ->with('error', 'Folder not found.');
        }
    }

    /**
     * Update the specified folder in storage.
     */
    public function update(UpdateFolderRequest $request, string $id): RedirectResponse
    {
        try {
            $folder = Folder::findOrFail($id);
            $this->authorize('update', $folder);
            $validatedData = $request->validated();

            // Keep existing color if not provided
            if (empty($validatedData['color_code'])) {
                $validatedData['color_code'] = $folder->color_code;
            }

            $folder->update($validatedData);

            return redirect()
                ->route('folders.show', $folder->id)
                ->with('success', "Folder '{$folder->name}' has been updated successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('folders.index')
                ->with('error', 'Folder not found.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update folder. Please try again.');
        }
    }

    /**
     * Remove the specified folder from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $folder = Folder::findOrFail($id);
            $this->authorize('delete', $folder);
            $folderName = $folder->name;
            $resourceCount = $folder->resources()->count();

            $folder->delete(); // Cascade delete will remove all resources

            return redirect()
                ->route('folders.index')
                ->with('success', "Folder '{$folderName}' and {$resourceCount} resource(s) have been deleted successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('folders.index')
                ->with('error', 'Folder not found.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete folder. Please try again.');
        }
    }
}
