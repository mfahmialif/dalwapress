<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditColumns;

#[Fillable(['name', 'description', 'status', 'created_by', 'updated_by', 'deleted_by'])]
class Role extends Model
{
    use SoftDeletes, HasAuditColumns;
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
