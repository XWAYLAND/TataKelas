<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Folder;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NoteController extends Controller
{
    /**
     * Display a listing of notes.
     */
    public function index(Request $request): View
    {
        $query = Note::with('folder');

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Folder filter
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->input('folder_id'));
        }

        $notes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $folders = Folder::orderBy('name')->get();

        return view('notes.index', compact('notes', 'folders'));
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(Request $request): View
    {
        $folders = Folder::orderBy('name')->get();
        $selectedFolderId = $request->input('folder_id');

        return view('notes.create', compact('folders', 'selectedFolderId'));
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            $note = Note::create($validatedData);

            return redirect()
                ->route('folders.show', $note->folder_id)
                ->with('success', "Note '{$note->title}' has been created successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create note. Please try again.');
        }
    }

    /**
     * Display the specified note.
     */
    public function show(string $id): View|RedirectResponse
    {
        try {
            $note = Note::with('folder')->findOrFail($id);
            return view('notes.show', compact('note'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('notes.index')
                ->with('error', 'Note not found.');
        }
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $note = Note::findOrFail($id);
            $folders = Folder::orderBy('name')->get();

            return view('notes.edit', compact('note', 'folders'));
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('notes.index')
                ->with('error', 'Note not found.');
        }
    }

    /**
     * Update the specified note in storage.
     */
    public function update(UpdateNoteRequest $request, string $id): RedirectResponse
    {
        try {
            $note = Note::findOrFail($id);
            $validatedData = $request->validated();
            
            $note->update($validatedData);

            return redirect()
                ->route('folders.show', $note->folder_id)
                ->with('success', "Note '{$note->title}' has been updated successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('notes.index')
                ->with('error', 'Note not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update note. Please try again.');
        }
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $note = Note::findOrFail($id);
            $noteTitle = $note->title;
            $folderId = $note->folder_id;

            $note->delete();

            return redirect()
                ->route('folders.show', $folderId)
                ->with('success', "Note '{$noteTitle}' has been deleted successfully!");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('notes.index')
                ->with('error', 'Note not found.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete note. Please try again.');
        }
    }
}
