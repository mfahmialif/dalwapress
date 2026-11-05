<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;
use App\Traits\CascadeSoftDeletes;

class Submission extends Model
{
    use SoftDeletes, HasAuditColumns, CascadeSoftDeletes;

    protected array $cascadeSoftDeletes = [
        'reviews', 'revisions', 'editorAssignments',
        'layouterAssignments', 'coverAssignments',
        'manuscriptVersions', 'editorEvaluations', 'editorialDecisions',
        'submissionAuthors',
    ];

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
        'current_round',
        'submitted_at',
        'reviewed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'current_round' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissionAuthors(): HasMany
    {
        return $this->hasMany(SubmissionAuthor::class)->orderBy('sort_order', 'asc');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'submission_authors')
            ->withPivot(['name', 'email', 'phone', 'institution', 'role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order', 'asc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubmissionReview::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SubmissionRevision::class);
    }

    public function manuscriptVersions(): HasMany
    {
        return $this->hasMany(SubmissionManuscriptVersion::class)->orderBy('version_number', 'asc');
    }

    public function editorEvaluations(): HasMany
    {
        return $this->hasMany(SubmissionEditorEvaluation::class)->orderBy('created_at', 'asc');
    }

    public function editorialDecisions(): HasMany
    {
        return $this->hasMany(SubmissionEditorialDecision::class)->orderBy('round', 'asc');
    }

    public function editorAssignments(): HasMany
    {
        return $this->hasMany(SubmissionEditorAssignment::class);
    }

    public function layouterAssignments(): HasMany
    {
        return $this->hasMany(SubmissionLayouterAssignment::class);
    }

    public function coverAssignments(): HasMany
    {
        return $this->hasMany(SubmissionCoverAssignment::class);
    }

    public function primaryEditorAssignment()
    {
        return $this->hasOne(SubmissionEditorAssignment::class)->where('role', 'primary');
    }
}
