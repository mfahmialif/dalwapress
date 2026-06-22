<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'name', 'email', 'email_verified_at', 'password', 'role_id', 'status', 'last_active_at', 'google_id', 'avatar_url', 'email_verification_code', 'email_verification_expires_at'])]
#[Hidden(['password', 'remember_token', 'email_verification_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function author(): HasOne
    {
        return $this->hasOne(Author::class);
    }

    public function editorAssignments(): HasMany
    {
        return $this->hasMany(SubmissionEditorAssignment::class, 'editor_id');
    }

    public function authoredNews(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    public function createdNews(): HasMany
    {
        return $this->hasMany(News::class, 'created_by');
    }
}
