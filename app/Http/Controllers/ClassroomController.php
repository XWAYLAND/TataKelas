<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinClassroomRequest;
use App\Http\Requests\StoreClassFolderRequest;
use App\Http\Requests\StoreClassroomRequest;
use App\Models\Classroom;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        $classrooms = auth()->user()->isTeacher()
            ? auth()->user()->teachingClassrooms()->withCount('students')->latest()->get()
            : auth()->user()->classrooms()->with('teacher')->withCount('folders')->latest()->get();

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
        $classroom->students()->syncWithoutDetaching([$request->user()->id]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', "You joined {$classroom->name}.");
    }

    public function show(Classroom $classroom): View
    {
        $this->authorize('view', $classroom);

        $folders = $classroom->folders()
            ->whereIn('visibility', ['class_material', 'assignment'])
            ->withCount(['resources', 'submissions'])
            ->orderByRaw("CASE WHEN visibility = 'assignment' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();

        $students = auth()->user()->isTeacher()
            ? $classroom->students()->orderBy('name')->get()
            : collect();

        return view('classrooms.show', compact('classroom', 'folders', 'students'));
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

        $folder->load('resources');

        $isTeacher = (int) $classroom->teacher_id === (int) auth()->id();
        $submission = $isTeacher
            ? null
            : $folder->submissions()->where('student_id', auth()->id())->first();
        $submissions = $isTeacher
            ? $folder->submissions()->with('student')->latest()->get()
            : collect();
        $pendingStudents = $isTeacher
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
            'isTeacher',
            'submission',
            'submissions',
            'pendingStudents',
            'submissionStats'
        ));
    }
}
