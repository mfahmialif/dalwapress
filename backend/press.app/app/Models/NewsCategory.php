<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class NewsCategory extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_category_news')
            ->withTimestamps();
    }

    public function legacyNews(): HasMany
    {
        return $this->hasMany(News::class, 'news_category_id');
    }
}
