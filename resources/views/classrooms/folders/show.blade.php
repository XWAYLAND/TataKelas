@extends('layouts.app')

@section('title', $folder->name)

@section('content')
    <div class="space-y-7">
        <a href="{{ route('classrooms.show', $classroom) }}" class="inline-flex items-center gap-2 text-sm font-medium text-base-content/55 hover:text-primary">
            <i class="fas fa-arrow-left text-xs"></i> {{ $classroom->name }}
        </a>

        <header class="app-surface overflow-hidden">
            <div class="h-1.5 {{ $folder->visibility === 'assignment' ? 'bg-warning' : 'bg-accent' }}"></div>
            <div class="p-5 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="break-words text-2xl font-bold tracking-tight sm:text-3xl">{{ $folder->name }}</h1>
                            <span class="badge {{ $folder->visibility === 'assignment' ? 'badge-warning badge-outline' : 'badge-info badge-outline' }}">
                                {{ $folder->visibility === 'assignment' ? 'Assignment' : 'Learning materials' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-base-content/55">{{ $classroom->name }} · {{ $classroom->teacher->name }}</p>
                    </div>
                    @if($folder->visibility === 'assignment' && $folder->due_date)
                        <div class="shrink-0 rounded-xl {{ $folder->due_date->isPast() ? 'bg-error/10 text-error' : 'bg-base-200 text-base-content' }} px-4 py-3">
                            <p class="text-xs font-medium opacity-70">Due date</p>
                            <p class="mt-1 font-semibold">{{ $folder->due_date->format('M j, Y · g:i A') }}</p>
                            @if($folder->due_date->isPast())<p class="mt-1 text-xs">The deadline has passed; late submissions are marked.</p>@endif
                        </div>
                    @endif
                </div>
                @if($folder->description)
                    <p class="mt-5 max-w-3xl whitespace-pre-line text-sm leading-6 text-base-content/70">{{ $folder->description }}</p>
                @endif
            </div>
        </header>

        @if($folder->resources->isNotEmpty() || $canManage)
            <section class="app-surface overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300/70 p-5">
                    <div>
                        <h2 class="font-semibold">Learning materials</h2>
                        <p class="mt-1 text-sm text-base-content/55">Resources shared with this classroom.</p>
                    </div>
                </div>
                @if($folder->resources->isNotEmpty())
                    <ul class="divide-y divide-base-300/70">
                        @foreach($folder->resources as $resource)
                            <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h3 class="font-medium">{{ $resource->title }}</h3>
                                    <p class="mt-1 text-sm text-base-content/55">{{ $resource->notes }}</p>
                                </div>
                                @if($resource->file_path)
                                    <a class="btn btn-outline btn-sm shrink-0" href="{{ route('resources.file', $resource) }}">
                                        <i class="fas fa-download"></i> Download file
                                    </a>
                                @elseif($resource->url)
                                    <a class="btn btn-outline btn-sm shrink-0" href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-arrow-up-right-from-square"></i> Open resource
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-5 py-6 text-sm text-base-content/55">No learning resources have been added to this folder.</p>
                @endif

                @if($canManage)
                    <form action="{{ route('classrooms.folders.resources.store', [$classroom, $folder]) }}" method="POST" enctype="multipart/form-data" class="grid gap-4 border-t border-base-300/70 bg-base-200/35 p-5 md:grid-cols-2">
                        @csrf
                        <h3 class="font-semibold md:col-span-2">Add a resource</h3>
                        <label class="form-control">
                            <span class="label-text mb-2 font-medium">Title</span>
                            <input class="input input-bordered w-full" name="title" required maxlength="255" placeholder="e.g. Chapter 1 slides">
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-2 font-medium">Type</span>
                            <select class="select select-bordered w-full" name="resource_type" required>
                                <option value="url">Link</option>
                                <option value="file">PDF / document</option>
                                <option value="video">Video</option>
                                <option value="documentation">Documentation</option>
                                <option value="assignment_instruction">Assignment instruction</option>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-2 font-medium">URL <span class="text-base-content/45">(leave blank for file upload)</span></span>
                            <input class="input input-bordered w-full" type="url" name="url" placeholder="https://...">
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-2 font-medium">PDF or document</span>
                            <input class="file-input file-input-bordered w-full" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                            <span class="mt-1 text-xs text-base-content/50">Maximum 20 MB. Files are only downloadable by class members.</span>
                        </label>
                        <label class="form-control md:col-span-2">
                            <span class="label-text mb-2 font-medium">Description <span class="text-base-content/45">(optional)</span></span>
                            <textarea class="textarea textarea-bordered w-full" name="notes" rows="2" maxlength="5000"></textarea>
                        </label>
                        @if($errors->any())
                            <div class="alert alert-error md:col-span-2"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
                        @endif
                        <div class="md:col-span-2 md:text-right"><button class="btn btn-primary"><i class="fas fa-plus"></i> Add resource</button></div>
                    </form>
                @endif
            </section>
        @endif

        @if($folder->visibility === 'assignment' && $canManage)
            <section class="app-surface overflow-hidden">
                <div class="border-b border-base-300/70 p-5">
                    <h2 class="font-semibold">Student submissions</h2>
                    <p class="mt-1 text-sm text-base-content/55">Review work, assign a score, and send feedback.</p>
                </div>
                <dl class="grid grid-cols-3 divide-x divide-base-300/70 border-b border-base-300/70 text-center">
                    <div class="py-4"><dt class="text-xs text-base-content/55">Submitted</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ $submissionStats['submitted'] }}</dd></div>
                    <div class="py-4"><dt class="text-xs text-base-content/55">Graded</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ $submissionStats['graded'] }}</dd></div>
                    <div class="py-4"><dt class="text-xs text-base-content/55">Pending</dt><dd class="mt-1 text-xl font-semibold tabular-nums">{{ $submissionStats['pending'] }}</dd></div>
                </dl>
                <div class="divide-y divide-base-300/70">
                    @foreach($pendingStudents as $pendingStudent)
                        <article class="flex flex-wrap items-center justify-between gap-3 p-5">
                            <div>
                                <h3 class="font-semibold">{{ $pendingStudent->name }}</h3>
                                <p class="mt-1 text-xs text-base-content/50">{{ $pendingStudent->email }}</p>
                            </div>
                            <span class="badge badge-ghost">Awaiting submission</span>
                        </article>
                    @endforeach
                    @forelse($submissions as $studentSubmission)
                        <article class="grid gap-4 p-5 lg:grid-cols-[1fr_auto]">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold">{{ $studentSubmission->student->name }}</h3>
                                    <span class="badge {{ $studentSubmission->status === 'graded' ? 'badge-success' : ($studentSubmission->status === 'late' ? 'badge-error' : 'badge-info') }}">{{ ucfirst($studentSubmission->status) }}</span>
                                    @if(!is_null($studentSubmission->grade))<span class="badge badge-outline">Grade {{ $studentSubmission->grade }}/100</span>@endif
                                </div>
                                <p class="mt-1 text-xs text-base-content/50">Submitted {{ $studentSubmission->created_at->format('M j, Y · g:i A') }}</p>
                                @if($studentSubmission->submission_text)<p class="mt-3 whitespace-pre-line text-sm leading-6">{{ $studentSubmission->submission_text }}</p>@endif
                                @if($studentSubmission->submitted_url)<a class="mt-2 inline-flex items-center gap-2 text-sm text-primary hover:underline" href="{{ $studentSubmission->submitted_url }}" target="_blank" rel="noopener noreferrer">Open submitted link <i class="fas fa-arrow-up-right-from-square text-xs"></i></a>@endif
                                @if($studentSubmission->files->isNotEmpty())
                                    <ul class="mt-2 space-y-1">
                                        @foreach($studentSubmission->files as $file)
                                            <li>
                                                <a class="btn btn-ghost btn-sm max-w-full justify-start" href="{{ route('submissions.files.download', $file) }}">
                                                    <i class="fas fa-download shrink-0"></i>
                                                    <span class="truncate">{{ $file->original_name }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="lg:self-start">
                                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('grade-{{ $studentSubmission->id }}').showModal()">
                                    <i class="fas fa-pen-to-square"></i> {{ $studentSubmission->status === 'graded' ? 'Edit grade' : 'Review and grade' }}
                                </button>
                                <dialog id="grade-{{ $studentSubmission->id }}" class="modal">
                                    <div class="modal-box max-w-xl">
                                        <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" aria-label="Close"><i class="fas fa-xmark"></i></button></form>
                                        <h3 class="pr-10 text-lg font-bold">Grade {{ $studentSubmission->student->name }}</h3>
                                        <p class="mt-1 text-sm text-base-content/55">{{ $folder->name }}</p>
                                        <form action="{{ route('submissions.grade', $studentSubmission) }}" method="POST" class="mt-5 space-y-4">
                                            @csrf
                                            @method('PATCH')
                                            <label class="form-control">
                                                <span class="label-text mb-2 font-medium">Grade / 100</span>
                                                <input class="input input-bordered w-full" name="grade" type="number" min="0" max="100" step="1" value="{{ $studentSubmission->grade }}" required>
                                            </label>
                                            <label class="form-control">
                                                <span class="label-text mb-2 font-medium">Feedback</span>
                                                <textarea class="textarea textarea-bordered w-full" name="teacher_feedback" rows="4" maxlength="10000">{{ $studentSubmission->teacher_feedback }}</textarea>
                                            </label>
                                            <div class="modal-action">
                                                <button type="button" class="btn btn-ghost" onclick="document.getElementById('grade-{{ $studentSubmission->id }}').close()">Cancel</button>
                                                <button class="btn btn-primary"><i class="fas fa-check"></i> Save grade</button>
                                            </div>
                                        </form>
                                    </div>
                                    <form method="dialog" class="modal-backdrop"><button>close</button></form>
                                </dialog>
                            </div>
                        </article>
                    @empty
                        @if($pendingStudents->isEmpty())
                            <p class="px-5 py-8 text-center text-sm text-base-content/55">No students are enrolled in this classroom yet.</p>
                        @endif
                    @endforelse
                </div>
            </section>
        @elseif($folder->visibility === 'assignment')
            <section class="app-surface p-5 sm:p-6">
                @if($submission)
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold">Your submission</h2>
                        <span class="badge {{ $submission->status === 'graded' ? 'badge-success' : ($submission->status === 'late' ? 'badge-error' : 'badge-info') }}">{{ ucfirst($submission->status) }}</span>
                        @if(!is_null($submission->grade))<span class="badge badge-outline">Grade {{ $submission->grade }}/100</span>@endif
                    </div>
                    <p class="mt-2 text-sm text-base-content/55">Submitted {{ $submission->created_at->format('M j, Y · g:i A') }}. Update it below, or unsend it if you need to start over.</p>
                    @if($submission->teacher_feedback)
                        <div class="mt-4 rounded-xl bg-success/10 p-4">
                            <p class="text-sm font-semibold">Teacher feedback</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-6">{{ $submission->teacher_feedback }}</p>
                        </div>
                    @endif
                @else
                    <h2 class="font-semibold">Submit your work</h2>
                    <p class="mt-1 text-sm text-base-content/55">Add a written response, a link, or upload up to 10 files. Late submissions are accepted and marked late.</p>
                @endif

                @if($errors->any())
                    <div role="alert" class="alert alert-error mt-4"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
                @endif

                <form action="{{ route('folders.submissions.store', $folder) }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4">
                    @csrf
                    <label class="form-control">
                        <span class="label-text mb-2 font-medium">Written response <span class="text-base-content/45">(optional if you attach a file or link)</span></span>
                        <textarea class="textarea textarea-bordered min-h-28 w-full" name="submission_text" rows="4" maxlength="10000">{{ old('submission_text', $submission?->submission_text) }}</textarea>
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="form-control">
                            <label for="submission-files" class="label-text mb-2 font-medium">Attach files</label>
                            <input id="submission-files" class="file-input file-input-bordered w-full" type="file" name="files[]" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" multiple>
                            <span class="mt-1 text-xs text-base-content/50">PDF, Office documents, or ZIP · up to 10 files · max 20 MB each</span>
                            <p id="submission-files-warning" class="mt-1 hidden text-xs font-medium text-warning">Only the first 10 files are kept. Remove one file before adding another.</p>
                            <div id="submission-files-panel" class="mt-2 hidden overflow-hidden rounded-xl border border-base-300/80 bg-base-100">
                                <p class="border-b border-base-300/70 px-3 py-2 text-xs font-medium text-base-content/65">
                                    Selected files <span id="submission-files-count" class="text-base-content/45"></span>
                                </p>
                                <ul id="submission-files-items" class="divide-y divide-base-300/70"></ul>
                            </div>
                        </div>
                        <label class="form-control">
                            <span class="label-text mb-2 font-medium">Or submit a link</span>
                            <input class="input input-bordered w-full" type="url" name="submitted_url" value="{{ old('submitted_url', $submission?->submitted_url) }}" placeholder="https://...">
                        </label>
                    </div>
                    @if($submission?->files->isNotEmpty())
                        <div class="rounded-xl bg-base-200/50 p-3">
                            <p class="text-sm font-medium">Current attachments</p>
                            <p class="mt-1 text-xs text-base-content/50">Uploading new files replaces these attachments.</p>
                            <ul class="mt-2 space-y-1">
                                @foreach($submission->files as $file)
                                    <li>
                                        <a class="link link-primary inline-flex max-w-full items-center gap-2 text-sm" href="{{ route('submissions.files.download', $file) }}">
                                            <i class="fas fa-paperclip shrink-0"></i>
                                            <span class="truncate">{{ $file->original_name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ $submission ? 'Update submission' : 'Submit assignment' }}</button>
                    </div>
                </form>
                @if($submission)
                    <form action="{{ route('folders.submissions.destroy', $folder) }}" method="POST" class="mt-3" onsubmit="return confirm('Unsend this assignment? Your submission and all attached files will be deleted.');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline btn-error" type="submit"><i class="fas fa-rotate-left"></i> Unsend assignment</button>
                    </form>
                @endif
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var input = document.getElementById('submission-files');
            if (!input || typeof DataTransfer === 'undefined') return;

            var maxFiles = 10;
            var selected = [];
            var panel = document.getElementById('submission-files-panel');
            var items = document.getElementById('submission-files-items');
            var count = document.getElementById('submission-files-count');
            var warning = document.getElementById('submission-files-warning');

            function formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }

            function syncInput() {
                var transfer = new DataTransfer();
                selected.forEach(function (file) {
                    transfer.items.add(file);
                });
                input.files = transfer.files;
            }

            function render() {
                items.innerHTML = '';
                panel.classList.toggle('hidden', selected.length === 0);
                count.textContent = selected.length ? '(' + selected.length + ' / ' + maxFiles + ')' : '';

                selected.forEach(function (file, index) {
                    var row = document.createElement('li');
                    row.className = 'flex items-center justify-between gap-3 px-3 py-2';

                    var info = document.createElement('div');
                    info.className = 'min-w-0';

                    var name = document.createElement('p');
                    name.className = 'truncate text-sm font-medium';
                    name.textContent = file.name;

                    var meta = document.createElement('p');
                    meta.className = 'text-xs text-base-content/50';
                    meta.textContent = formatSize(file.size);

                    info.append(name, meta);

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-ghost btn-xs shrink-0 text-error';
                    remove.setAttribute('aria-label', 'Remove ' + file.name);
                    remove.innerHTML = '<i class="fas fa-xmark"></i> Remove';
                    remove.addEventListener('click', function () {
                        selected.splice(index, 1);
                        syncInput();
                        render();
                        warning.classList.add('hidden');
                    });

                    row.append(info, remove);
                    items.appendChild(row);
                });
            }

            input.addEventListener('change', function () {
                var hitLimit = false;

                Array.prototype.forEach.call(input.files, function (file) {
                    if (selected.length >= maxFiles) {
                        hitLimit = true;
                        return;
                    }

                    var duplicate = selected.some(function (existing) {
                        return existing.name === file.name
                            && existing.size === file.size
                            && existing.lastModified === file.lastModified;
                    });

                    if (!duplicate) selected.push(file);
                });

                syncInput();
                render();
                warning.classList.toggle('hidden', !hitLimit);
            });
        })();
    </script>
@endpush
