<?php

namespace Database\Factories;

use App\Enums\PeminjamanStatus;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $tanggalPinjam = $this->faker->dateTimeBetween('-1 month', 'now');
        $tanggalKembaliRencana = (clone $tanggalPinjam)->modify('+' . rand(1, 7) . ' days');

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'nomor_peminjaman' => 'P-' . $this->faker->unique()->numerify('#####'),
            'tanggal_pinjam' => $tanggalPinjam,
            'tanggal_kembali_rencana' => $tanggalKembaliRencana,
            'tanggal_kembali_real' => null,
            'status' => $this->faker->randomElement(PeminjamanStatus::cases()),
            'approved_by' => null,
            'rejected_by' => null,
        ];
    }

    public function dipinjam()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PeminjamanStatus::Disetujui,
                'approved_by' => User::factory(),
            ];
        });
    }

    public function selesai()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PeminjamanStatus::Kembali,
                'tanggal_kembali_real' => $attributes['tanggal_kembali_rencana'],
                'approved_by' => User::factory(),
            ];
        });
    }

    public function menunggu()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PeminjamanStatus::Menunggu,
                'approved_by' => null,
            ];
        });
    }
}
