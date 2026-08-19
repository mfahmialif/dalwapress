<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class SubmissionEditorEvaluation extends Model
{
    use HasFactory, SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'editor_id',
        'round',
        'recommendation',
        'notes',
        'submitted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
