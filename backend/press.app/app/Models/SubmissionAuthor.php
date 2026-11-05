<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionAuthor extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'author_id',
        'name',
        'email',
        'phone',
        'institution',
        'role',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
