<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Folder;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    /**
     * Display a listing of photos.
     */
    public function index(Request $request): View
    {
        $query = Photo::with('folder');

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Folder filter
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->input('folder_id'));
        }

        $photos = $query->orderBy('created_at', 'desc')->paginate(12);
        $folders = Folder::orderBy('name')->get();

        return view('photos.index', compact('photos', 'folders'));
    }

    /**
     * Show the form for creating a new photo.
     */
    public function create(Request $request): View
    {
        $folders = Folder::orderBy('name')->get();
        $selectedFolderId = $request->input('folder_id');

        return view('photos.create', compact('folders', 'selectedFolderId'));
    }

    /**
     * Store a newly created photo in storage.
     */
    public function store(StorePhotoRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Handle file upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filepath = $file->storeAs('photos', $filename, 'public');
                
                $validatedData['filename'] = $file->getClientOriginalName();
                $validatedData['filepath'] = $filepath;
                $validatedData['mime_type'] = $file->getMimeType();
                $validatedData['file_size'] = $file->getSize();
            }
            
            unset($validatedData['photo']);
            $photo = Photo::create($validatedData);

            return redirect()
                ->route('folders.show', $photo->folder_id)
                ->with('success', "Photo '{$photo->title}' has been uploaded successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to upload photo. Please try again. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified photo.
     */
    public function show(string $id): View|RedirectResponse
    {
        try {
            $photo = Photo::with('folder')->findOrFail($id);
            return view('photos.show', compact('photo'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('photos.index')
                ->with('error', 'Photo not found.');
        }
    }

    /**
     * Show the form for editing the specified photo.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $photo = Photo::findOrFail($id);
            $folders = Folder::orderBy('name')->get();

            return view('photos.edit', compact('photo', 'folders'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('photos.index')
                ->with('error', 'Photo not found.');
        }
    }

    /**
     * Update the specified photo in storage.
     */
    public function update(UpdatePhotoRequest $request, string $id): RedirectResponse
    {
        try {
            $photo = Photo::findOrFail($id);
            $validatedData = $request->validated();
            
            // Handle new file upload if provided
            if ($request->hasFile('photo')) {
                // Delete old file
                if (Storage::exists($photo->filepath)) {
                    Storage::delete($photo->filepath);
                }
                
                // Upload new file
                $file = $request->file('photo');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filepath = $file->storeAs('photos', $filename, 'public');
                
                $validatedData['filename'] = $file->getClientOriginalName();
                $validatedData['filepath'] = $filepath;
                $validatedData['mime_type'] = $file->getMimeType();
                $validatedData['file_size'] = $file->getSize();
            }
            
            unset($validatedData['photo']);
            $photo->update($validatedData);

            return redirect()
                ->route('folders.show', $photo->folder_id)
                ->with('success', "Photo '{$photo->title}' has been updated successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('photos.index')
                ->with('error', 'Photo not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update photo. Please try again.');
        }
    }

    /**
     * Remove the specified photo from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $photo = Photo::findOrFail($id);
            $photoTitle = $photo->title;
            $folderId = $photo->folder_id;

            $photo->delete(); // File deletion handled by model boot method

            return redirect()
                ->route('folders.show', $folderId)
                ->with('success', "Photo '{$photoTitle}' has been deleted successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('photos.index')
                ->with('error', 'Photo not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete photo. Please try again.');
        }
    }
}
