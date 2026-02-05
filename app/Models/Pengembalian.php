<?php

namespace App\Models;

use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengembalian extends Model
{
    use MencatatAktivitas;
    protected $guarded = [];
    protected $casts = [
        'tanggal_kembali_real' => 'date',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengembalianDetail::class);
    }
}
