@extends('layouts.app')

@section('title', 'Create Note')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <nav>
            <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('notes.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
        </nav>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-warning">New note</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Create New Note</h1>
            <p class="mt-2 text-sm text-base-content/60">Write down an idea, summary, or reminder and attach it to a folder.</p>
        </div>

        <form action="{{ route('notes.store') }}" method="POST" class="app-surface space-y-6 p-5 sm:p-6">
            @csrf

            <div>
                <label for="folder_id" class="mb-2 block text-sm font-medium">Folder <span class="text-error">*</span></label>
                <select name="folder_id" id="folder_id" class="select select-bordered w-full @error('folder_id') select-error @enderror" required>
                    <option value="">Choose a folder</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ old('folder_id', $selectedFolderId) == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                    @endforeach
                </select>
                @error('folder_id')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="mb-2 block text-sm font-medium">Note title <span class="text-error">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                       class="input input-bordered w-full @error('title') input-error @enderror"
                       placeholder="e.g., Project Ideas, Meeting Notes, Study Summary" required autofocus>
                @error('title')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="priority" class="mb-2 block text-sm font-medium">Priority level <span class="text-error">*</span></label>
                <select name="priority" id="priority" class="select select-bordered w-full @error('priority') select-error @enderror" required>
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low priority</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium priority</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High priority</option>
                </select>
                @error('priority')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="content" class="mb-2 block text-sm font-medium">Note content <span class="text-error">*</span></label>
                <textarea name="content" id="content" rows="12"
                          class="textarea textarea-bordered w-full font-mono text-sm leading-relaxed @error('content') textarea-error @enderror"
                          placeholder="Write your note content here...&#10;&#10;You can add lists, code snippets, ideas, reminders, or anything you need to remember."
                          required>{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-base-content/45"><i class="fas fa-circle-info mr-1"></i> Plain text is preserved, including line breaks.</p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-base-300/70 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ $selectedFolderId ? route('folders.show', $selectedFolderId) : route('notes.index') }}" class="btn btn-ghost border-base-300">Cancel</a>
                <button type="submit" class="btn btn-warning"><i class="fas fa-check"></i> Save Note</button>
            </div>
        </form>
    </div>
@endsection
