<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function view(User $user, Resource $resource): bool
    {
        return $resource->folder !== null
            && app(FolderPolicy::class)->view($user, $resource->folder);
    }

    public function create(User $user, Folder $folder): bool
    {
        return app(FolderPolicy::class)->update($user, $folder);
    }

    public function update(User $user, Resource $resource): bool
    {
        return $resource->folder !== null
            && app(FolderPolicy::class)->update($user, $resource->folder);
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $this->update($user, $resource);
    }
}
