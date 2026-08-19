<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class SubmissionEditorialDecision extends Model
{
    use HasFactory, SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'round',
        'decision',
        'notes',
        'decided_by',
        'decided_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
