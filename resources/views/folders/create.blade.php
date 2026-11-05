@extends('layouts.app')

@section('title', 'Create Folder')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <nav>
            <a href="{{ route('folders.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 transition hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Back to dashboard
            </a>
        </nav>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">New workspace</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Create New Folder</h1>
            <p class="mt-2 text-sm text-base-content/60">Give this workspace a name, a short description, and a color.</p>
        </div>

        <form action="{{ route('folders.store') }}" method="POST" class="app-surface space-y-6 p-5 sm:p-6">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium">Folder name <span class="text-error">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="input input-bordered w-full @error('name') input-error @enderror"
                       placeholder="e.g., Laravel Documentation, React Tutorials" required autofocus>
                @error('name')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                          placeholder="Brief description of what this folder contains...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-base-content/45">Optional — helps you recognize the folder at a glance.</p>
            </div>

            <div>
                <label for="color_code" class="mb-2 block text-sm font-medium">Folder color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color_code" id="color_code" value="{{ old('color_code', '#3B82F6') }}"
                           class="h-11 w-16 cursor-pointer rounded-lg border border-base-300 bg-base-100 p-1">
                    <input type="text" id="color_code_text" value="{{ old('color_code', '#3B82F6') }}"
                           class="input input-bordered w-full font-mono text-sm" placeholder="#3B82F6" readonly>
                </div>
                @error('color_code')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-base-content/50">Quick picks:</span>
                    <button type="button" onclick="setColor('#3B82F6')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-blue-500 transition hover:scale-110" title="Blue"></button>
                    <button type="button" onclick="setColor('#10B981')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-emerald-500 transition hover:scale-110" title="Green"></button>
                    <button type="button" onclick="setColor('#F59E0B')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-amber-500 transition hover:scale-110" title="Amber"></button>
                    <button type="button" onclick="setColor('#EF4444')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-red-500 transition hover:scale-110" title="Red"></button>
                    <button type="button" onclick="setColor('#8B5CF6')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-violet-500 transition hover:scale-110" title="Violet"></button>
                    <button type="button" onclick="setColor('#EC4899')" class="h-7 w-7 rounded-full border-2 border-base-300 bg-pink-500 transition hover:scale-110" title="Pink"></button>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-base-300/70 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('folders.index') }}" class="btn btn-ghost border-base-300">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create Folder</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const colorPicker = document.getElementById('color_code');
        const colorText = document.getElementById('color_code_text');

        colorPicker.addEventListener('input', function () {
            colorText.value = this.value.toUpperCase();
        });

        function setColor(color) {
            colorPicker.value = color;
            colorText.value = color;
        }
    </script>
@endpush
