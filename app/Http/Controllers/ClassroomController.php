<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinClassroomRequest;
use App\Http\Requests\StoreClassFolderRequest;
use App\Http\Requests\StoreClassroomRequest;
use App\Models\Classroom;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isTeacher()) {
            $classrooms = Classroom::query()
                ->where(function ($query) use ($user): void {
                    $query->where('teacher_id', $user->id)
                        ->orWhereHas('coTeachers', fn ($coTeachers) => $coTeachers->whereKey($user->id));
                })
                ->with('teacher')
                ->withCount(['students', 'folders'])
                ->latest()
                ->get();
        } else {
            $classrooms = $user->classrooms()->with('teacher')->withCount('folders')->latest()->get();
        }

        return view('classrooms.index', compact('classrooms'));
    }

    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        $classroom = $request->user()->teachingClassrooms()->create([
            ...$request->validated(),
            'code' => Str::upper(Str::random(8)),
        ]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Classroom created. Share the join code with your students.');
    }

    public function join(JoinClassroomRequest $request): RedirectResponse
    {
        $classroom = Classroom::where('code', $request->validated('code'))->firstOrFail();
        $user = $request->user();

        if ($user->isTeacher()) {
            if ($classroom->isOwnedBy($user)) {
                return redirect()
                    ->route('classrooms.show', $classroom)
                    ->with('info', 'You already teach this classroom.');
            }

            $classroom->coTeachers()->syncWithoutDetaching([$user->id]);

            return redirect()
                ->route('classrooms.show', $classroom)
                ->with('success', "You joined {$classroom->name} as a co-teacher.");
        }

        $classroom->students()->syncWithoutDetaching([$user->id]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', "You joined {$classroom->name}.");
    }

    public function show(Classroom $classroom): View
    {
        $user = auth()->user();
        $this->authorize('view', $classroom);

        $isOwner = $classroom->isOwnedBy($user);
        $canManage = $user->can('createClassFolder', $classroom);

        $folders = $classroom->folders()
            ->whereIn('visibility', ['class_material', 'assignment'])
            ->withCount(['resources', 'submissions'])
            ->orderByRaw("CASE WHEN visibility = 'assignment' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();

        $students = $canManage
            ? $classroom->students()->orderBy('name')->get()
            : collect();

        $coTeachers = $canManage
            ? $classroom->coTeachers()->orderBy('name')->get()
            : collect();

        return view('classrooms.show', compact(
            'classroom',
            'folders',
            'students',
            'coTeachers',
            'isOwner',
            'canManage'
        ));
    }

    public function removeCoTeacher(Classroom $classroom, User $coTeacher): RedirectResponse
    {
        $this->authorize('removeCoTeacher', [$classroom, $coTeacher]);

        $classroom->coTeachers()->detach($coTeacher->id);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', "{$coTeacher->name} was removed as a co-teacher.");
    }

    public function leaveCoTeaching(Classroom $classroom): RedirectResponse
    {
        $user = auth()->user();

        if (! $classroom->coTeachers()->whereKey($user->id)->exists()) {
            return redirect()
                ->route('classrooms.index')
                ->with('info', 'You are not a co-teacher of this classroom.');
        }

        $classroom->coTeachers()->detach($user->id);

        return redirect()
            ->route('classrooms.index')
            ->with('success', "You left {$classroom->name} as a co-teacher.");
    }

    public function storeFolder(StoreClassFolderRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorize('createClassFolder', $classroom);

        $folder = $request->user()->folders()->create([
            ...$request->validated(),
            'classroom_id' => $classroom->id,
            'color_code' => $request->validated('color_code') ?: '#3B82F6',
        ]);

        return redirect()
            ->route('classrooms.folders.show', [$classroom, $folder])
            ->with('success', 'Class folder created.');
    }

    public function showFolder(Classroom $classroom, Folder $folder): View
    {
        $this->authorize('view', $classroom);
        abort_unless((int) $folder->classroom_id === (int) $classroom->id, 404);
        $this->authorize('view', $folder);

        $folder->load(['resources.files']);

        $canManage = auth()->user()->can('createClassFolder', $classroom);
        $submission = $canManage
            ? null
            : $folder->submissions()->with('files')->where('student_id', auth()->id())->first();
        $submissions = $canManage
            ? $folder->submissions()->with(['student', 'files'])->latest()->get()
            : collect();
        $pendingStudents = $canManage
            ? $classroom->students()
                ->whereNotIn('users.id', $submissions->pluck('student_id'))
                ->orderBy('name')
                ->get()
            : collect();
        $submissionStats = [
            'submitted' => $submissions->whereIn('status', ['submitted', 'late'])->count(),
            'graded' => $submissions->where('status', 'graded')->count(),
            'pending' => $pendingStudents->count(),
        ];

        return view('classrooms.folders.show', compact(
            'classroom',
            'folder',
            'canManage',
            'submission',
            'submissions',
            'pendingStudents',
            'submissionStats'
        ));
    }
}
