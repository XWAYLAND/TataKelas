<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('owned', function (Builder $query): void {
            if ($user = Auth::user()) {
                $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery
                    ->withoutGlobalScope('accessible')
                    ->accessibleBy($user));
            }
        });

        static::deleting(function (Resource $resource): void {
            if ($resource->file_path) {
                Storage::disk('local')->delete($resource->file_path);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folder_id',
        'title',
        'url',
        'file_path',
        'resource_type',
        'notes',
        'read_status',
    ];

    /**
     * Get the folder that owns the resource.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Determine whether the attached material can be shown inline in a browser.
     */
    public function getIsPreviewableAttribute(): bool
    {
        if (! $this->file_path) {
            return false;
        }

        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Get the resource type badge color.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->resource_type) {
            'url' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            'file' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
            'assignment_instruction' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
            'documentation' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            'video' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    /**
     * Get the read status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->read_status) {
            'to_read' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
            'reading' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }
}
