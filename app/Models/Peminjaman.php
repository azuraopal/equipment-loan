<?php

namespace App\Models;

use App\Enums\PeminjamanStatus;
use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use MencatatAktivitas;

    protected $table = 'peminjamans';

    protected $guarded = [];

    protected $casts = [
        'status' => PeminjamanStatus::class,
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
