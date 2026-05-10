<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'author_name',
        'email',
        'phone',
        'manuscript_file',
        'cover_file',
        'description',
        'note',
        'status',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubmissionReview::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SubmissionRevision::class);
    }

    public function editorAssignments(): HasMany
    {
        return $this->hasMany(SubmissionEditorAssignment::class);
    }

    public function primaryEditorAssignment()
    {
        return $this->hasOne(SubmissionEditorAssignment::class)->where('role', 'primary');
    }
}
