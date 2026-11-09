<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            // Database cascade deletes file rows without firing model events,
            // so remove the physical files before the resource is deleted.
            $resource->files()->get()->each->delete();
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
     * Get the attached material files (PDF/Office documents).
     */
    public function files(): HasMany
    {
        return $this->hasMany(ResourceFile::class);
    }

    /**
     * Determine whether any attached material can be shown inline in a browser.
     */
    public function getIsPreviewableAttribute(): bool
    {
        return $this->files->contains(fn (ResourceFile $file) => $file->is_previewable);
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

    /**
     * Store uploaded material files for the resource on the private disk.
     *
     * @param  iterable<int, UploadedFile>  $uploads
     */
    public function storeUploadedFiles(iterable $uploads, string $directory): void
    {
        foreach ($uploads as $upload) {
            $path = $upload->storeAs($directory, Str::uuid().'.'.$upload->extension(), 'local');

            $this->files()->create([
                'file_path' => $path,
                'original_name' => Str::limit($upload->getClientOriginalName(), 255, ''),
                'size' => $upload->getSize(),
            ]);
        }
    }

    /**
     * Replace every attached file with a fresh set of uploads.
     *
     * @param  iterable<int, UploadedFile>  $uploads
     */
    public function replaceUploadedFiles(iterable $uploads, string $directory): void
    {
        $this->files()->get()->each->delete();
        $this->storeUploadedFiles($uploads, $directory);
    }
}
