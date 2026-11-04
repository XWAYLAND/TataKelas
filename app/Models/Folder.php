<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Get the count of resources in the folder.
     */
    public function getResourcesCountAttribute(): int
    {
        return $this->resources()->count();
    }

    /**
     * Get the count of notes in the folder.
     */
    public function getNotesCountAttribute(): int
    {
        return $this->notes()->count();
    }

    /**
     * Get the count of photos in the folder.
     */
    public function getPhotosCountAttribute(): int
    {
        return $this->photos()->count();
    }

    /**
     * Get total items count in the folder.
     */
    public function getTotalItemsCountAttribute(): int
    {
        return $this->resources()->count() + $this->notes()->count() + $this->photos()->count();
    }
}
