<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FolderController extends Controller
{
    /**
     * Display a listing of folders (Dashboard).
     */
    public function index(): View
    {
        $folders = Folder::withCount('resources')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('folders.index', compact('folders'));
    }

    /**
     * Show the form for creating a new folder.
     */
    public function create(): View
    {
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
    public function show(string $id): View|RedirectResponse
    {
        try {
            $folder = Folder::with([
                'resources' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'notes' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'photos' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->findOrFail($id);

            // Get all folders for resource move dropdown
            $folders = Folder::where('id', '!=', $id)
                ->orderBy('name')
                ->get();

            // Statistics
            $stats = [
                'total' => $folder->resources->count(),
                'to_read' => $folder->resources->where('read_status', 'to_read')->count(),
                'reading' => $folder->resources->where('read_status', 'reading')->count(),
                'completed' => $folder->resources->where('read_status', 'completed')->count(),
            ];

            // Resource type counts
            $typeStats = [
                'documentation' => $folder->resources->where('resource_type', 'documentation')->count(),
                'article' => $folder->resources->where('resource_type', 'article')->count(),
                'repository' => $folder->resources->where('resource_type', 'repository')->count(),
                'video' => $folder->resources->where('resource_type', 'video')->count(),
                'other' => $folder->resources->where('resource_type', 'other')->count(),
            ];

            // Item counts
            $itemCounts = [
                'resources' => $folder->resources->count(),
                'notes' => $folder->notes->count(),
                'photos' => $folder->photos->count(),
                'total' => $folder->resources->count() + $folder->notes->count() + $folder->photos->count(),
            ];

            return view('folders.show', compact('folder', 'folders', 'stats', 'typeStats', 'itemCounts'));
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
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete folder. Please try again.');
        }
    }
}
