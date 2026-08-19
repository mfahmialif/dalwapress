<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class SubmissionManuscriptVersion extends Model
{
    use HasFactory, SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'user_id',
        'version_number',
        'round',
        'file_path',
        'note',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
