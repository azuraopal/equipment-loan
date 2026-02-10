<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'json' => 'array',
        'transaction_time' => 'datetime',
    ];

    public function pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class);
    }
}
