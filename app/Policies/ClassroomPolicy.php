<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        return (int) $classroom->teacher_id === (int) $user->id
            || $classroom->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->isTeacher()
            && (int) $classroom->teacher_id === (int) $user->id;
    }
}
