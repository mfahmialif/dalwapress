<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class SubmissionCoverAssignment extends Model
{
    use HasFactory, SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'cover_designer_id',
        'assigned_by',
        'note',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cover_designer_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
