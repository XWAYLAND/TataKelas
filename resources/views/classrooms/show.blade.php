@extends('layouts.app')

@section('title', $classroom->name)

@section('content')
    <div class="space-y-7">
        <a href="{{ route('classrooms.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 hover:text-primary">
            <i class="fas fa-arrow-left text-xs"></i> All classrooms
        </a>

        <header class="app-surface overflow-hidden">
            <div class="h-1.5 bg-primary"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-7 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-primary/10 text-xl text-primary"><i class="fas fa-chalkboard-user"></i></span>
                        <h1 class="break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $classroom->name }}</h1>
                    </div>
                    @if($classroom->description)
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-base-content/65">{{ $classroom->description }}</p>
                    @endif
                    @if(!auth()->user()->isTeacher())
                        <p class="mt-3 text-sm text-base-content/55">Taught by {{ $classroom->teacher->name }}</p>
                    @endif
                </div>

                @if((int) $classroom->teacher_id === (int) auth()->id())
                    <div class="shrink-0 rounded-xl bg-base-200 px-4 py-3">
                        <p class="text-xs font-medium text-base-content/55">Student join code</p>
                        <p class="mt-1 select-all font-mono text-lg font-bold tracking-[0.16em]">{{ $classroom->code }}</p>
                    </div>
                @endif
            </div>
        </header>

        @if((int) $classroom->teacher_id === (int) auth()->id())
            <form action="{{ route('classrooms.folders.store', $classroom) }}" method="POST" class="app-surface grid gap-4 p-5 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold">Add course content</h2>
                    <p class="mt-1 text-sm text-base-content/55">Create a shared materials folder or an assignment for enrolled students.</p>
                </div>
                <label class="form-control">
                    <span class="label-text mb-2 font-medium">Folder name</span>
                    <input class="input input-bordered w-full" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Week 1 — Cell Structure">
                    @error('name')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-2 font-medium">Content type</span>
                    <select class="select select-bordered w-full" name="visibility" required>
                        <option value="class_material" @selected(old('visibility') === 'class_material')>Learning materials</option>
                        <option value="assignment" @selected(old('visibility') === 'assignment')>Assignment</option>
                    </select>
                    @error('visibility')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <label class="form-control md:col-span-2">
                    <span class="label-text mb-2 font-medium">Instructions or description <span class="text-base-content/45">(optional)</span></span>
                    <textarea class="textarea textarea-bordered w-full" name="description" rows="3" maxlength="1000" placeholder="Explain what students will learn or submit">{{ old('description') }}</textarea>
                    @error('description')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-2 font-medium">Due date <span class="text-base-content/45">(for assignments)</span></span>
                    <input class="input input-bordered w-full" type="datetime-local" name="due_date" value="{{ old('due_date') }}">
                    @error('due_date')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <div class="flex items-end justify-end">
                    <button class="btn btn-primary"><i class="fas fa-folder-plus"></i> Create folder</button>
                </div>
            </form>
        @endif

        <section>
            <div class="mb-4 flex items-baseline justify-between gap-3">
                <h2 class="text-lg font-semibold">Materials and assignments</h2>
                <span class="text-sm text-base-content/50">{{ $folders->count() }} folders</span>
            </div>
            @if($folders->isEmpty())
                <div class="rounded-2xl border border-dashed border-base-300 bg-base-100/70 px-6 py-10 text-center">
                    <p class="font-medium">Nothing posted here yet</p>
                    <p class="mt-1 text-sm text-base-content/55">{{ auth()->user()->isTeacher() ? 'Create a folder above to share your first materials.' : 'Your teacher has not shared materials or assignments yet.' }}</p>
                </div>
            @else
                <div class="divide-y divide-base-300/70 rounded-2xl border border-base-300/80 bg-base-100">
                    @foreach($folders as $folder)
                        <a href="{{ route('classrooms.folders.show', [$classroom, $folder]) }}" class="group flex flex-col gap-3 p-5 transition hover:bg-base-200/50 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $folder->visibility === 'assignment' ? 'bg-warning/10 text-warning' : 'bg-accent/10 text-accent' }}">
                                    <i class="fas {{ $folder->visibility === 'assignment' ? 'fa-clipboard-check' : 'fa-book-open' }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold group-hover:text-primary">{{ $folder->name }}</h3>
                                    <p class="mt-1 line-clamp-2 text-sm text-base-content/55">{{ $folder->description ?: ($folder->visibility === 'assignment' ? 'Assignment' : 'Course materials') }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-3 sm:pl-4">
                                @if($folder->visibility === 'assignment')
                                    @if($folder->due_date)
                                        <span class="text-xs {{ $folder->due_date->isPast() ? 'text-error' : 'text-base-content/55' }}">
                                            Due {{ $folder->due_date->format('M j, g:i A') }}
                                        </span>
                                    @endif
                                    <span class="badge badge-warning badge-outline">Assignment</span>
                                @else
                                    <span class="badge badge-info badge-outline">Materials</span>
                                @endif
                                <i class="fas fa-arrow-right text-xs text-primary transition group-hover:translate-x-1"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        @if((int) $classroom->teacher_id === (int) auth()->id())
            <details class="app-surface group">
                <summary class="cursor-pointer list-none p-5 font-semibold">
                    <i class="fas fa-users mr-2 text-primary"></i>Students enrolled
                    <span class="ml-1 text-sm font-normal text-base-content/50">{{ $students->count() }}</span>
                </summary>
                <div class="divide-y divide-base-300/70 border-t border-base-300/70">
                    @forelse($students as $student)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <span class="font-medium">{{ $student->name }}</span>
                            <span class="text-sm text-base-content/55">{{ $student->email }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-base-content/55">Students who join with your class code will appear here.</p>
                    @endforelse
                </div>
            </details>
        @endif
    </div>
@endsection
