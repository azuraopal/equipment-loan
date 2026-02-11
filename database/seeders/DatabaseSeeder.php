<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\PeminjamanAlat;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Models\Payment;
use App\Enums\UserRole;
use App\Enums\PeminjamanStatus;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->command->info('Truncating tables...');
        User::truncate();
        Kategori::truncate();
        Alat::truncate();
        Peminjaman::truncate();
        PeminjamanAlat::truncate();
        Pengembalian::truncate();
        PengembalianDetail::truncate();
        Payment::truncate();

        Schema::enableForeignKeyConstraints();

        $this->command->info('Starting Seeder...');
        $this->command->info('Creating Users...');
        $admin = User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $petugas = User::firstOrCreate([
            'email' => 'petugas@test.com'
        ], [
            'name' => 'Petugas Lab',
            'password' => Hash::make('password'),
            'role' => UserRole::Petugas,
        ]);

        $member = User::firstOrCreate([
            'email' => 'peminjam@test.com'
        ], [
            'name' => 'Mahasiswa Santuy',
            'password' => Hash::make('password'),
            'role' => UserRole::Peminjam,
        ]);

        if (User::where('role', UserRole::Peminjam)->count() < 10) {
            $this->command->info('Creating Users manually...');
            $users = collect();
            for ($i = 0; $i < 10; $i++) {
                $users->push(User::create([
                    'name' => 'User ' . $i,
                    'email' => 'user' . $i . '@test.com',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Peminjam,
                ]));
            }
        } else {
            $users = User::where('role', UserRole::Peminjam)->get();
        }

        if (Kategori::count() == 0) {
            $this->command->info('Creating Categories...');
            $categories = Kategori::factory()->count(5)->create();
        } else {
            $categories = Kategori::all();
        }

        if (Alat::count() == 0) {
            $this->command->info('Creating Alats...');
            $alats = Alat::factory()->count(20)->recycle($categories)->create();
        } else {
            $alats = Alat::all();
        }

        if ($alats->count() > 0) {

            $this->command->info('Creating Pending Loans...');
            $pendingLoans = Peminjaman::factory()->count(5)->create([
                'user_id' => fn() => $users->random()->id,
                'status' => PeminjamanStatus::Menunggu,
                'tanggal_pinjam' => now()->addDays(1),
                'tanggal_kembali_rencana' => now()->addDays(3),
            ]);
            $this->attachAlats($pendingLoans, $alats);

            $approvedLoans = Peminjaman::factory()->count(5)->create([
                'user_id' => fn() => $users->random()->id,
                'status' => PeminjamanStatus::Disetujui,
                'approved_by' => $petugas->id,
                'tanggal_pinjam' => now()->subDays(1),
                'tanggal_kembali_rencana' => now()->addDays(2),
            ]);

            foreach ($approvedLoans as $loan) {
                $alat = $alats->random();
                if ($alat->isStockAvailable(1)) {
                    $loan->alats()->attach($alat->id, ['jumlah' => 1]);
                    $alat->decrement('stok', 1);
                }
            }

            $rejectedLoans = Peminjaman::factory()->count(3)->create([
                'user_id' => fn() => $users->random()->id,
                'status' => PeminjamanStatus::Ditolak,
                'rejected_by' => $petugas->id,
            ]);
            $this->attachAlats($rejectedLoans, $alats);

            $completedLoans = Peminjaman::factory()->count(10)->create([
                'user_id' => fn() => $users->random()->id,
                'status' => PeminjamanStatus::Kembali,
                'approved_by' => $petugas->id,
                'tanggal_pinjam' => now()->subMonth(),
                'tanggal_kembali_rencana' => now()->subMonth()->addDays(3),
                'tanggal_kembali_real' => now()->subMonth()->addDays(3),
            ]);

            foreach ($completedLoans as $loan) {
                $alat = $alats->random();
                $loan->alats()->attach($alat->id, ['jumlah' => 1]);

                $pengembalian = Pengembalian::factory()->create([
                    'peminjaman_id' => $loan->id,
                    'petugas_id' => $petugas->id,
                    'tanggal_kembali_real' => $loan->tanggal_kembali_real,
                    'denda_keterlambatan' => 0,
                    'total_denda' => 0,
                    'status_pembayaran' => 'Lunas',
                ]);

                PengembalianDetail::create([
                    'pengembalian_id' => $pengembalian->id,
                    'alat_id' => $alat->id,
                    'kondisi_kembali' => 'Baik',
                    'jumlah_kembali' => 1,
                ]);
            }

            $lateLoans = Peminjaman::factory()->count(5)->create([
                'user_id' => fn() => $users->random()->id,
                'status' => PeminjamanStatus::Kembali,
                'approved_by' => $petugas->id,
                'tanggal_pinjam' => now()->subMonth(),
                'tanggal_kembali_rencana' => now()->subMonth()->addDays(3),
                'tanggal_kembali_real' => now()->subMonth()->addDays(5),
            ]);

            foreach ($lateLoans as $loan) {
                $alat = $alats->random();
                $loan->alats()->attach($alat->id, ['jumlah' => 1]);

                $denda = 50000;
                $pengembalian = Pengembalian::factory()->create([
                    'peminjaman_id' => $loan->id,
                    'petugas_id' => $petugas->id,
                    'tanggal_kembali_real' => $loan->tanggal_kembali_real,
                    'denda_keterlambatan' => $denda,
                    'total_denda' => $denda,
                    'status_pembayaran' => 'Lunas',
                ]);

                PengembalianDetail::create([
                    'pengembalian_id' => $pengembalian->id,
                    'alat_id' => $alat->id,
                    'kondisi_kembali' => 'Baik',
                    'jumlah_kembali' => 1,
                ]);

                Payment::factory()->create([
                    'pengembalian_id' => $pengembalian->id,
                    'amount' => $denda,
                    'payment_type' => 'cash',
                    'status' => 'settlement',
                ]);
            }
        }
    }

    private function attachAlats($loans, $alats)
    {
        foreach ($loans as $loan) {
            $loan->alats()->attach($alats->random()->id, ['jumlah' => 1]);
        }
    }
}