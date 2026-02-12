<?php
namespace App\Models;

use App\Traits\MencatatAktivitas;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;

class Alat extends Model
{
    use HasFactory, MencatatAktivitas;
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

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function peminjamans()
    {
        return $this->belongsToMany(Peminjaman::class, 'peminjaman_alats', 'alat_id', 'peminjaman_id');
    }

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

        static::deleting(function ($alat) {
            if ($alat->peminjamans()->exists()) {
                Notification::make()
                    ->danger()
                    ->title('Gagal Menghapus')
                    ->body('Tidak dapat menghapus alat karena sedang dipinjam atau memiliki riwayat peminjaman.')
                    ->send();
                return false;
            }
        });
    }
}