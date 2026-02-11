<?php
namespace App\Models;

use App\Traits\MencatatAktivitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory, MencatatAktivitas;
    protected $guarded = [];

    public function alats(): HasMany
    {
        return $this->hasMany(Alat::class);
    }
}