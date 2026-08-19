<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class SubmissionEditorAssignment extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'submission_id',
        'editor_id',
        'assigned_by',
        'role',
        'note',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
