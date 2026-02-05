<?php

namespace App\Models;

use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengembalianDetail extends Model
{
    use MencatatAktivitas;
    protected $guarded = [];

    public function pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class);
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class);
    }
}