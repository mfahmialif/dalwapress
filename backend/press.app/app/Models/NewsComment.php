<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;
use App\Traits\CascadeSoftDeletes;

class NewsComment extends Model
{
    use SoftDeletes, HasAuditColumns, CascadeSoftDeletes;

    protected array $cascadeSoftDeletes = ['replies', 'likes'];

    protected $fillable = ['news_id', 'parent_id', 'name', 'email', 'message', 'likes', 'created_by', 'updated_by', 'deleted_by'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }

    public function parent()
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(NewsComment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'news_comment_id');
    }
}
