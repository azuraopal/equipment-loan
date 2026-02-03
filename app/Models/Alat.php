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
}