<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSubmissionRequest;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Folder;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($request, $folder, $submission, $validated): void {
            $data = [
                'submission_text' => $validated['submission_text'] ?? null,
                'submitted_url' => $validated['submitted_url'] ?? null,
                'grade' => null,
                'teacher_feedback' => null,
                'status' => $folder->due_date && now()->greaterThan($folder->due_date) ? 'late' : 'submitted',
            ];

            if ($submission) {
                $submission->update($data);
            } else {
                $submission = $folder->submissions()->create([
                    ...$data,
                    'student_id' => $request->user()->id,
                ]);
            }

            if ($request->hasFile('files')) {
                $submission->files()->get()->each->delete();

                foreach ($request->file('files') as $file) {
                    $path = $file->storeAs('submissions', Str::uuid().'.'.$file->extension(), 'local');

                    $submission->files()->create([
                        'file_path' => $path,
                        'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('classrooms.folders.show', [$folder->classroom_id, $folder->id])
            ->with('success', 'Your assignment was submitted.');
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('submit', $folder);

        $submission = $folder->submissions()
            ->where('student_id', $request->user()->id)
            ->first();

        abort_unless($submission, 404);

        $submission->delete();

        return redirect()
            ->route('classrooms.folders.show', [$folder->classroom_id, $folder->id])
            ->with('success', 'Your submission was unsent.');
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

    public function file(SubmissionFile $file): StreamedResponse
    {
        $this->authorize('view', $file->submission);
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);

        return Storage::disk('local')->download($file->file_path, $file->original_name);
    }
}
