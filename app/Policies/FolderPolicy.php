<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function view(User $user, Folder $folder): bool
    {
        if ($folder->visibility === 'private') {
            return (int) $folder->user_id === (int) $user->id;
        }

        $classroom = $folder->classroom;

        return $classroom !== null
            && ((int) $classroom->teacher_id === (int) $user->id
                || $classroom->students()->whereKey($user->id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isStudent();
    }

    public function createClassFolder(User $user, Classroom $classroom): bool
    {
        return $user->isTeacher()
            && (int) $classroom->teacher_id === (int) $user->id;
    }

    public function update(User $user, Folder $folder): bool
    {
        if ($folder->visibility === 'private') {
            return (int) $folder->user_id === (int) $user->id;
        }

        return $folder->classroom !== null
            && (int) $folder->classroom->teacher_id === (int) $user->id;
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $this->update($user, $folder);
    }

    public function submit(User $user, Folder $folder): bool
    {
        return $user->role === 'student'
            && $folder->visibility === 'assignment'
            && $folder->classroom !== null
            && $folder->classroom->students()->whereKey($user->id)->exists();
    }
}
