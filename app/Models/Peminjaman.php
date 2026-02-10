<?php

namespace App\Models;

use App\Enums\PeminjamanStatus;
use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $tanggal_pinjam
 * @property \Illuminate\Support\Carbon $tanggal_kembali_rencana
 * @property \Illuminate\Support\Carbon|null $tanggal_kembali_real
 * @property PeminjamanStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PeminjamanAlat[] $peminjamanDetails
 */
class Peminjaman extends Model
{
    use MencatatAktivitas;

    protected $table = 'peminjamans';

    protected $guarded = [];

    protected $casts = [
        'status' => PeminjamanStatus::class,
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_real' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function peminjamanDetails()
    {
        return $this->hasMany(PeminjamanAlat::class, 'peminjaman_id');
    }

    public function alats()
    {
        return $this->belongsToMany(Alat::class, 'peminjaman_alats', 'peminjaman_id', 'alat_id')
            ->withPivot('jumlah');
    }

    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class);
    }
}
