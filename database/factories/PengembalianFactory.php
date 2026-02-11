<?php

namespace Database\Factories;

use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PengembalianFactory extends Factory
{
    protected $model = Pengembalian::class;

    public function definition(): array
    {
        return [
            'peminjaman_id' => Peminjaman::factory(),
            'nomor_pengembalian' => 'R-' . $this->faker->unique()->numerify('#####'),
            'tanggal_kembali_real' => Carbon::now(),
            'denda_keterlambatan' => 0,
            'denda_kerusakan' => 0,
            'denda_kehilangan' => 0,
            'total_denda' => 0,
            'status_pembayaran' => 'Lunas',
            'petugas_id' => User::factory(),
        ];
    }
}
