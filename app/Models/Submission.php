<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'student_id',
        'submission_text',
        'submitted_url',
        'grade',
        'teacher_feedback',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Submission $submission): void {
            $submission->files()->get()->each(function (SubmissionFile $file): void {
                $file->delete();
            });
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }
}
