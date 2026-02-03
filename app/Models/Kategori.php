<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $guarded = [];

    public function alats(): HasMany
    {
        return $this->hasMany(Alat::class);
    }
}