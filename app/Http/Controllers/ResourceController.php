<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassResourceRequest;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Models\Classroom;
use App\Models\Folder;
use App\Models\Resource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    /**
     * Display a listing of resources with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Resource::with('folder')
            ->whereHas('folder', fn ($folderQuery) => $folderQuery->whereNull('classroom_id'));

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

        $resources = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $folders = Folder::whereNull('classroom_id')
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return view('resources.index', compact('resources', 'folders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $folders = Folder::whereNull('classroom_id')
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();
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
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $validatedData['file_path'] = $file->storeAs(
                    'personal-resources',
                    Str::uuid().'.'.$file->extension(),
                    'local'
                );
            }
            unset($validatedData['file']);
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

    public function storeForClassroom(StoreClassResourceRequest $request, Classroom $classroom, Folder $folder): RedirectResponse
    {
        $this->authorize('create', [Resource::class, $folder]);

        $validated = $request->validated();
        $data = [
            'title' => $validated['title'],
            'url' => $validated['url'] ?? null,
            'resource_type' => $validated['resource_type'],
            'notes' => $validated['notes'] ?? null,
            'read_status' => 'to_read',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->storeAs(
                'class-materials',
                Str::uuid().'.'.$file->extension(),
                'local'
            );
        }

        $folder->resources()->create($data);

        return redirect()
            ->route('classrooms.folders.show', [$folder->classroom_id, $folder])
            ->with('success', 'Learning resource added.');
    }

    public function classroomFile(Resource $resource): StreamedResponse
    {
        $this->authorize('view', $resource);
        abort_unless($resource->file_path && Storage::disk('local')->exists($resource->file_path), 404);

        $extension = pathinfo($resource->file_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($resource->file_path, Str::slug($resource->title).'.'.$extension);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        try {
            $resource = Resource::with('folder')->findOrFail($id);
            $this->authorize('view', $resource);

            if ($resource->folder->classroom_id) {
                return redirect()->route('classrooms.folders.show', [
                    $resource->folder->classroom_id,
                    $resource->folder_id,
                ]);
            }

            return view('resources.show', compact('resource'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        } catch (AuthorizationException $e) {
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $resource = Resource::findOrFail($id);
            $this->authorize('update', $resource);
            $folders = Folder::whereNull('classroom_id')
                ->where('user_id', auth()->id())
                ->orderBy('name')
                ->get();

            return view('resources.edit', compact('resource', 'folders'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        } catch (AuthorizationException $e) {
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResourceRequest $request, string $id): RedirectResponse
    {
        try {
            $resource = Resource::findOrFail($id);
            $this->authorize('update', $resource);
            $validatedData = $request->validated();
            if ($request->hasFile('file')) {
                if ($resource->file_path) {
                    Storage::disk('local')->delete($resource->file_path);
                }
                $file = $request->file('file');
                $validatedData['file_path'] = $file->storeAs(
                    'personal-resources',
                    Str::uuid().'.'.$file->extension(),
                    'local'
                );
            }
            unset($validatedData['file']);
            $resource->update($validatedData);

            return redirect()
                ->route('folders.show', $resource->folder_id)
                ->with('success', "Resource '{$resource->title}' has been updated successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('resources.index')
                ->with('error', 'Resource not found.');
        } catch (AuthorizationException $e) {
            throw $e;
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
            $this->authorize('delete', $resource);
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
        } catch (AuthorizationException $e) {
            throw $e;
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
                'read_status' => ['required', 'in:to_read,reading,completed'],
            ]);

            $resource = Resource::findOrFail($id);
            $this->authorize('update', $resource);
            $resource->update(['read_status' => $request->input('read_status')]);

            return redirect()
                ->back()
                ->with('success', 'Read status updated successfully!');
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'Resource not found.');
        } catch (AuthorizationException $e) {
            throw $e;
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
                'folder_id' => [
                    'required',
                    Rule::exists('folders', 'id')
                        ->where('user_id', $request->user()->id)
                        ->whereNull('classroom_id')
                        ->where('visibility', 'private'),
                ],
            ]);

            $resource = Resource::findOrFail($id);
            $this->authorize('update', $resource);
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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to move resource. Please try again.');
        }
    }
}
