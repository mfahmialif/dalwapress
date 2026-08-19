<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class CommentLike extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = ['user_id', 'news_comment_id', 'created_by', 'updated_by', 'deleted_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(NewsComment::class, 'news_comment_id');
    }
}
