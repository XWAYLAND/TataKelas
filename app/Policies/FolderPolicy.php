<?php

namespace App\Policies;

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
            && ($classroom->hasTeacherAccess($user)
                || $classroom->students()->whereKey($user->id)->exists());
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isStudent();
    }

    public function update(User $user, Folder $folder): bool
    {
        if ($folder->visibility === 'private') {
            return (int) $folder->user_id === (int) $user->id;
        }

        $classroom = $folder->classroom;

        return $user->isTeacher()
            && $classroom !== null
            && $classroom->hasTeacherAccess($user);
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
