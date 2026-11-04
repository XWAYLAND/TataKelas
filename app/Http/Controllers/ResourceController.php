<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Folder;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResourceController extends Controller
{
    /**
     * Display a listing of resources with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Resource::with('folder');

        // Search filter (title and notes)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('notes', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('url', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Resource type filter
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->input('resource_type'));
        }

        // Read status filter
        if ($request->filled('read_status')) {
            $query->where('read_status', $request->input('read_status'));
        }

        // Folder filter
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->input('folder_id'));
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(20);
        $folders = Folder::orderBy('name')->get();

        return view('resources.index', compact('resources', 'folders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $folders = Folder::orderBy('name')->get();
        $selectedFolderId = $request->input('folder_id');

        return view('resources.create', compact('folders', 'selectedFolderId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResourceRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $resource = Resource::create($validatedData);

            return redirect()
                ->route('folders.show', $resource->folder_id)
                ->with('success', "Resource '{$resource->title}' has been added successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create resource. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        try {
            $resource = Resource::with('folder')->findOrFail($id);
            return view('resources.show', compact('resource'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $resource = Resource::findOrFail($id);
            $folders = Folder::orderBy('name')->get();

            return view('resources.edit', compact('resource', 'folders'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResourceRequest $request, string $id): RedirectResponse
    {
        try {
            $resource = Resource::findOrFail($id);
            $validatedData = $request->validated();
            
            $resource->update($validatedData);

            return redirect()
                ->route('folders.show', $resource->folder_id)
                ->with('success', "Resource '{$resource->title}' has been updated successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update resource. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $resource = Resource::findOrFail($id);
            $resourceTitle = $resource->title;
            $folderId = $resource->folder_id;

            $resource->delete();

            return redirect()
                ->route('folders.show', $folderId)
                ->with('success', "Resource '{$resourceTitle}' has been deleted successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete resource. Please try again.');
        }
    }

    /**
     * Update the read status of a resource (Quick toggle).
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        try {
            $request->validate([
                'read_status' => ['required', 'in:to_read,reading,completed']
            ]);

            $resource = Resource::findOrFail($id);
            $resource->update(['read_status' => $request->input('read_status')]);

            return redirect()
                ->back()
                ->with('success', 'Read status updated successfully!');
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'Resource not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update status. Please try again.');
        }
    }

    /**
     * Move resource to another folder.
     */
    public function move(Request $request, string $id): RedirectResponse
    {
        try {
            $request->validate([
                'folder_id' => ['required', 'exists:folders,id']
            ]);

            $resource = Resource::findOrFail($id);
            $oldFolderId = $resource->folder_id;
            $newFolderId = $request->input('folder_id');

            if ($oldFolderId == $newFolderId) {
                return redirect()
                    ->back()
                    ->with('info', 'Resource is already in this folder.');
            }

            $resource->update(['folder_id' => $newFolderId]);

            return redirect()
                ->route('folders.show', $newFolderId)
                ->with('success', "Resource '{$resource->title}' has been moved successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'Resource not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to move resource. Please try again.');
        }
    }
}
