<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Royalty extends Model
{
    protected $fillable = [
        'book_id',
        'author_id',
        'period_month',
        'period_year',
        'sold_qty',
        'sale_price_per_unit',
        'royalty_per_unit',
        'gross_amount',
        'royalty_amount',
        'status',
        'paid_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sale_price_per_unit' => 'decimal:2',
        'royalty_per_unit' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'royalty_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
