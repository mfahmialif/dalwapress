<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;
use App\Traits\CascadeSoftDeletes;

class Book extends Model
{
    use SoftDeletes, HasAuditColumns, CascadeSoftDeletes;

    protected array $cascadeSoftDeletes = ['galleries'];

    protected $appends = [
        'cover_url',
        'author_names',
    ];

    protected $fillable = [
        'category_id',
        'author_id',
        'submission_id',
        'title',
        'slug',
        'isbn',
        'year',
        'publisher',
        'pages',
        'size',
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
        'created_by',
        'updated_by',
        'deleted_by',
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

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order', 'asc');
    }

    public function getAuthorNamesAttribute(): string
    {
        if ($this->relationLoaded('authors') && $this->authors->isNotEmpty()) {
            return $this->authors->pluck('name')->join(', ');
        }

        return $this->author?->name ?? '-';
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(BookGallery::class);
    }

    public function royalties(): HasMany
    {
        return $this->hasMany(Royalty::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover) {
            return null;
        }

        if (str_starts_with($this->cover, 'http://') || str_starts_with($this->cover, 'https://')) {
            return $this->cover;
        }

        if (str_starts_with($this->cover, '/')) {
            return url($this->cover);
        }

        return asset(Storage::url($this->cover));
    }
}
