<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Folder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'color_code',
        'classroom_id',
        'visibility',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('accessible', function (Builder $query): void {
            if ($user = Auth::user()) {
                $query->accessibleBy($user);
            }
        });

        static::creating(function (Folder $folder): void {
            if (Auth::check()) {
                $folder->user_id = Auth::id();
            }
        });

        // Database cascade deletes photo rows without firing model events,
        // so remove the physical files before the folder is deleted.
        static::deleting(function (Folder $folder): void {
            $folder->photos()->get()->each(function (Photo $photo): void {
                $photo->delete();
            });
            $folder->resources()->get()->each(function (Resource $resource): void {
                $resource->delete();
            });
            $folder->submissions()->get()->each(function (Submission $submission): void {
                if ($submission->file_path) {
                    Storage::disk('local')->delete($submission->file_path);
                }
            });
        });
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where($query->qualifyColumn('user_id'), $user->getKey())
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->whereIn($query->qualifyColumn('visibility'), ['class_material', 'assignment'])
                        ->whereExists(function ($classrooms) use ($user): void {
                            $classrooms->selectRaw('1')
                                ->from('classrooms')
                                ->whereColumn('classrooms.id', 'folders.classroom_id')
                                ->where(function ($classrooms) use ($user): void {
                                    $classrooms->where('classrooms.teacher_id', $user->getKey())
                                        ->orWhereExists(function ($membership) use ($user): void {
                                            $membership->selectRaw('1')
                                                ->from('classroom_user')
                                                ->whereColumn('classroom_user.classroom_id', 'classrooms.id')
                                                ->where('classroom_user.user_id', $user->getKey());
                                        });
                                });
                        });
                });
        });
    }

    /**
     * Get the user that owns the folder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get all resources for the folder.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Get all notes for the folder.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all photos for the folder.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
