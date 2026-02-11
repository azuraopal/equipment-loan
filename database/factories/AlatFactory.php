<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlatFactory extends Factory
{
    protected $model = Alat::class;

    public function definition(): array
    {
        return [
            'kategori_id' => Kategori::factory(),
            'nama_alat' => $this->faker->words(3, true),
            'stok' => $this->faker->numberBetween(1, 50),
            'harga_satuan' => $this->faker->numberBetween(10000, 1000000),
            'kondisi_awal' => $this->faker->randomElement(['Baru', 'Baik', 'Cukup', 'Rusak Ringan']),
            'spesifikasi' => $this->faker->paragraph(),
            'gambar' => null,
        ];
    }
}
