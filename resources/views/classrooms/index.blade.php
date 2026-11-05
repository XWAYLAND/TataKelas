@extends('layouts.app')

@section('title', 'Classrooms')

@section('content')
    @php($isTeacher = auth()->user()->isTeacher())

    <div class="space-y-8">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Classrooms</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/60">
                    {{ $isTeacher
                        ? 'Create a classroom, or join another teacher’s class with its code to become a co-teacher.'
                        : 'Join a classroom to find your teacher’s materials and assignments.' }}
                </p>
            </div>
        </header>

        @if($isTeacher)
            <form action="{{ route('classrooms.store') }}" method="POST" class="app-surface grid gap-4 p-5 md:grid-cols-[1fr_1.5fr_auto] md:items-end">
                @csrf
                <label class="form-control">
                    <span class="label-text mb-2 font-medium">Classroom name</span>
                    <input class="input input-bordered w-full" name="name" value="{{ old('name') }}" placeholder="e.g. Biology 101" required maxlength="255">
                    @error('name')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-2 font-medium">Description <span class="text-base-content/45">(optional)</span></span>
                    <input class="input input-bordered w-full" name="description" value="{{ old('description') }}" placeholder="A short introduction to this course">
                    @error('description')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </label>
                <button class="btn btn-primary"><i class="fas fa-plus"></i> Create class</button>
            </form>
        @endif

        <form action="{{ route('classrooms.join') }}" method="POST" class="app-surface flex flex-col gap-3 p-5 sm:flex-row sm:items-end">
            @csrf
            <label class="form-control min-w-0 flex-1">
                <span class="label-text mb-2 font-medium">Class join code</span>
                <input class="input input-bordered w-full" name="code" value="{{ old('code') }}" placeholder="{{ $isTeacher ? 'Enter a code to co-teach another teacher’s class' : 'Enter the code shared by your teacher' }}" required maxlength="32" autocomplete="off">
                @error('code')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
            </label>
            <button class="btn btn-primary">
                <i class="fas fa-arrow-right-to-bracket"></i>
                {{ $isTeacher ? 'Join as co-teacher' : 'Join classroom' }}
            </button>
        </form>

        <section>
            <div class="mb-4 flex items-baseline justify-between gap-3">
                <h2 class="text-lg font-semibold">{{ $isTeacher ? 'Classes you teach or co-teach' : 'Your classrooms' }}</h2>
                <span class="text-sm text-base-content/50">{{ $classrooms->count() }} total</span>
            </div>

            @if($classrooms->isEmpty())
                <div class="rounded-2xl border border-dashed border-base-300 bg-base-100/70 px-6 py-12 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-primary/10 text-primary"><i class="fas fa-chalkboard"></i></span>
                    <h3 class="mt-4 font-semibold">No classrooms yet</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-base-content/55">
                        {{ $isTeacher
                            ? 'Create a classroom above, or enter a class code to co-teach an existing class.'
                            : 'Enter a class code above to join your first classroom.' }}
                    </p>
                </div>
            @else
                <div class="divide-y divide-base-300/70 rounded-2xl border border-base-300/80 bg-base-100">
                    @foreach($classrooms as $classroom)
                        @php($isOwner = (int) $classroom->teacher_id === (int) auth()->id())
                        <a href="{{ route('classrooms.show', $classroom) }}" class="group flex flex-col gap-3 p-5 transition hover:bg-base-200/50 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-4">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary"><i class="fas fa-chalkboard-user"></i></span>
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold group-hover:text-primary">{{ $classroom->name }}</h3>
                                    <p class="mt-1 truncate text-sm text-base-content/55">
                                        @if($isTeacher && $isOwner)
                                            Owner · {{ $classroom->students_count }} {{ \Illuminate\Support\Str::plural('student', $classroom->students_count) }}
                                        @elseif($isTeacher)
                                            Co-teacher · Owner: {{ $classroom->teacher->name }}
                                        @else
                                            Teacher: {{ $classroom->teacher->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-primary">
                                Open classroom <i class="fas fa-arrow-right text-xs transition group-hover:translate-x-1"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
