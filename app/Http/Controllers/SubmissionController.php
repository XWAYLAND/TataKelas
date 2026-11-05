<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSubmissionRequest;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Folder;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request, Folder $folder): RedirectResponse
    {
        $this->authorize('submit', $folder);

        $submission = $folder->submissions()
            ->where('student_id', $request->user()->id)
            ->first();

        $validated = $request->validated();
        $data = [
            'submission_text' => $validated['submission_text'] ?? null,
            'submitted_url' => $validated['submitted_url'] ?? null,
            'grade' => null,
            'teacher_feedback' => null,
            'status' => $folder->due_date && now()->greaterThan($folder->due_date) ? 'late' : 'submitted',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('submissions', Str::uuid().'.'.$file->extension(), 'local');
            $data['file_path'] = $path;
        }

        if ($submission && ! empty($data['file_path']) && $submission->file_path) {
            Storage::disk('local')->delete($submission->file_path);
        } elseif ($submission && empty($data['file_path'])) {
            $data['file_path'] = $submission->file_path;
        }

        if ($submission) {
            $submission->update($data);
        } else {
            $submission = $folder->submissions()->create([
                ...$data,
                'student_id' => $request->user()->id,
            ]);
        }

        return redirect()
            ->route('classrooms.folders.show', [$folder->classroom_id, $folder->id])
            ->with('success', 'Your assignment was submitted.');
    }

    public function grade(GradeSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $submission->update([
            ...$request->validated(),
            'status' => 'graded',
        ]);

        return redirect()
            ->route('classrooms.folders.show', [
                $submission->folder->classroom_id,
                $submission->folder_id,
            ])
            ->with('success', 'Submission graded.');
    }

    public function file(Submission $submission): StreamedResponse
    {
        $this->authorize('view', $submission);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path);
    }
}
