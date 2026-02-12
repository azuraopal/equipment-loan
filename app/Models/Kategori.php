<?php
namespace App\Models;

use App\Traits\MencatatAktivitas;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory, MencatatAktivitas;
    protected $guarded = [];

    protected static function booted()
    {
        static::deleting(function ($kategori) {
            if ($kategori->alats()->exists()) {
                Notification::make()
                    ->danger()
                    ->title('Gagal Menghapus')
                    ->body('Tidak dapat menghapus kategori karena masih memiliki alat terkait.')
                    ->send();
                return false;
            }
        });
    }

    public function alats(): HasMany
    {
        return $this->hasMany(Alat::class);
    }
}