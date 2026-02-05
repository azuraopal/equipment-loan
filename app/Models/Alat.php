<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    protected $guarded = [];

    public function isStockAvailable(int $requested): bool
    {
        return $this->stok >= $requested;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}