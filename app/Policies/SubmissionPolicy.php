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

        $classroom = $submission->folder?->classroom;

        return $user->isTeacher()
            && $classroom !== null
            && $classroom->hasTeacherAccess($user);
    }

    public function grade(User $user, Submission $submission): bool
    {
        $classroom = $submission->folder?->classroom;

        return $user->isTeacher()
            && $classroom !== null
            && $classroom->hasTeacherAccess($user);
    }
}
