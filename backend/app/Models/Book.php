<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'isbn',
        'year',
        'publisher',
        'pages',
        'language',
        'cover',
        'preview_file',
        'full_file',
        'description',
        'table_of_contents',
        'tags',
        'views',
        'downloads',
        'featured',
        'status',
        'published_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(BookGallery::class);
    }

    public function royalties(): HasMany
    {
        return $this->hasMany(Royalty::class);
    }
}
