<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookGallery extends Model
{
    public $timestamps = false;

    protected $fillable = ['book_id', 'image', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
