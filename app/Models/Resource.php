<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Resource extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('owned', function (Builder $query): void {
            if (Auth::check()) {
                $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery
                    ->where('user_id', Auth::id()));
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
     * Get the resource type badge color.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->resource_type) {
            'documentation' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            'article' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            'repository' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
            'video' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
            'other' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    /**
     * Get the read status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->read_status) {
            'to_read' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
            'reading' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }
}
