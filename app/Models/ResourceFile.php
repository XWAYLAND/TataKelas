<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ResourceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'file_path',
        'original_name',
        'size',
    ];

    protected static function booted(): void
    {
        static::deleting(function (ResourceFile $file): void {
            Storage::disk('local')->delete($file->file_path);
        });
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Determine whether this attached material can be shown inline in a browser.
     */
    public function getIsPreviewableAttribute(): bool
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }
}
