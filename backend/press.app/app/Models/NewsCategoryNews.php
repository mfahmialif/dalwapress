<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class NewsCategoryNews extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $table = 'news_category_news';

    protected $fillable = [
        'news_id', 'news_category_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }
}
