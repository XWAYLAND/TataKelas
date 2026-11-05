<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        if ((int) $submission->student_id === (int) $user->id) {
            $classroom = $submission->folder?->classroom;

            return $classroom !== null && $classroom->students()->whereKey($user->id)->exists();
        }

        return $user->isTeacher()
            && (int) $submission->folder?->classroom?->teacher_id === (int) $user->id;
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->isTeacher()
            && (int) $submission->folder?->classroom?->teacher_id === (int) $user->id;
    }
}
