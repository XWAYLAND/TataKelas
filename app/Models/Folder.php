<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('owned', function (Builder $query): void {
            if (Auth::check()) {
                $query->where($query->qualifyColumn('user_id'), Auth::id());
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
        });
    }

    /**
     * Get the user that owns the folder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
