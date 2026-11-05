<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('owned', function (Builder $query): void {
            if ($user = Auth::user()) {
                $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery
                    ->withoutGlobalScope('accessible')
                    ->whereNull('classroom_id')
                    ->accessibleBy($user));
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
        'filename',
        'filepath',
        'mime_type',
        'file_size',
        'description',
    ];

    /**
     * Get the folder that owns the photo.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the full URL to the photo.
     */
    public function getUrlAttribute(): string
    {
        return route('photos.file', $this->getKey());
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $size = $this->file_size;
        if ($size < 1024) {
            return $size.' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2).' KB';
        } else {
            return round($size / 1048576, 2).' MB';
        }
    }

    /**
     * Delete the photo file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            Storage::disk('local')->delete($photo->filepath);
            Storage::disk('public')->delete($photo->filepath);
        });
    }
}
