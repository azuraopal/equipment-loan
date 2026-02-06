<?php
namespace App\Models;

use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;
use Str;

class Alat extends Model
{
    use MencatatAktivitas;
    protected $guarded = [];

    protected $fillable = [
        'kategori_id',
        'kode_alat',
        'nama_alat',
        'stok',
        'harga_satuan',
        'kondisi_awal',
        'spesifikasi',
        'gambar'
    ];

    protected static function booted()
    {
        static::creating(function ($alat) {
            $prefix = 'ALT-';

            $lastRecord = self::where('kode_alat', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastRecord) {
                $lastNumber = (int) Str::after($lastRecord->kode_alat, $prefix);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $alat->kode_alat = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }

    public function isStockAvailable(int $requested): bool
    {
        return $this->stok >= $requested;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}