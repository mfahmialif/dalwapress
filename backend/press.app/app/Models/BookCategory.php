<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

class BookCategory extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = ['name', 'slug', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'category_id');
    }
}
