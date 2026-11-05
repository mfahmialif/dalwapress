<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class Author extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'phone',
        'photo',
        'bio',
        'institution',
        'social_media',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'social_media' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_authors')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }

    public function primaryBooks(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function submissionAuthors(): HasMany
    {
        return $this->hasMany(SubmissionAuthor::class);
    }

    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'submission_authors')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps();
    }

    public function royalties(): HasMany
    {
        return $this->hasMany(Royalty::class);
    }
}
