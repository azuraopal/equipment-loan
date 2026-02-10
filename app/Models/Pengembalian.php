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
        'tanggal_bayar' => 'date',
        'denda_keterlambatan' => 'decimal:2',
        'denda_kerusakan' => 'decimal:2',
        'denda_kehilangan' => 'decimal:2',
        'total_denda' => 'decimal:2',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengembalianDetail::class);
    }
}
