<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Note extends Model
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
        'content',
        'priority',
    ];

    /**
     * Get the folder that owns the note.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'high' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
            'low' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    /**
     * Get the priority icon.
     */
    public function getPriorityIconAttribute(): string
    {
        return match($this->priority) {
            'high' => 'fa-exclamation-circle',
            'medium' => 'fa-info-circle',
            'low' => 'fa-check-circle',
            default => 'fa-circle',
        };
    }
}
