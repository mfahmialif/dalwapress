<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class BookGallery extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = ['book_id', 'image', 'created_by', 'updated_by', 'deleted_by'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
