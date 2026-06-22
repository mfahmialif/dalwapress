<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'is_active',
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
