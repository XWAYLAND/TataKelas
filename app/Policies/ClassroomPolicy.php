<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        return $classroom->hasTeacherAccess($user)
            || $classroom->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->isTeacher()
            && $classroom->isOwnedBy($user);
    }

    public function createClassFolder(User $user, Classroom $classroom): bool
    {
        return $user->isTeacher()
            && $classroom->hasTeacherAccess($user);
    }

    public function removeCoTeacher(User $user, Classroom $classroom, User $coTeacher): bool
    {
        return $user->isTeacher()
            && $classroom->isOwnedBy($user)
            && ! $classroom->isOwnedBy($coTeacher)
            && $classroom->coTeachers()->whereKey($coTeacher->id)->exists();
    }
}
