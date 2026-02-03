<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\PeminjamanAlat;
use App\Enums\UserRole;
use App\Enums\PeminjamanStatus;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $petugas = User::create([
            'name' => 'Petugas Lab',
            'email' => 'petugas@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Petugas,
            'is_active' => true,
        ]);

        $peminjam = User::create([
            'name' => 'Mahasiswa Santuy',
            'email' => 'peminjam@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Peminjam,
            'is_active' => true,
        ]);

        $katElektronik = Kategori::create(['nama_kategori' => 'Elektronik', 'deskripsi' => 'Alat-alat listrik']);
        $katMekanik = Kategori::create(['nama_kategori' => 'Mekanik', 'deskripsi' => 'Alat pertukangan']);
        $katAV = Kategori::create(['nama_kategori' => 'Audio Visual', 'deskripsi' => 'Kamera dan Sound']);

        $laptop = Alat::create([
            'kategori_id' => $katElektronik->id,
            'nama_alat' => 'Laptop Asus ROG',
            'kode_alat' => 'LPT-001',
            'stok' => 10,
            'harga_satuan' => 15000000,
            'kondisi_awal' => 'Baik',
            'gambar' => null,
        ]);

        $bor = Alat::create([
            'kategori_id' => $katMekanik->id,
            'nama_alat' => 'Bor Listrik Bosch',
            'kode_alat' => 'BOR-001',
            'stok' => 2,
            'harga_satuan' => 500000,
            'kondisi_awal' => 'Baik',
        ]);

        $kamera = Alat::create([
            'kategori_id' => $katAV->id,
            'nama_alat' => 'Kamera Sony Alpha',
            'kode_alat' => 'CAM-001',
            'stok' => 5,
            'harga_satuan' => 25000000,
            'kondisi_awal' => 'Baik',
        ]);

        $pinjamBaru = Peminjaman::create([
            'user_id' => $peminjam->id,
            'nomor_peminjaman' => 'P-WAITING-001',
            'tanggal_pinjam' => now(),
            'tanggal_kembali_rencana' => now()->addDays(3),
            'status' => PeminjamanStatus::Menunggu,
            'keperluan' => 'Praktikum Jaringan Komputer',
        ]);

        PeminjamanAlat::create([
            'peminjaman_id' => $pinjamBaru->id,
            'alat_id' => $laptop->id,
            'jumlah' => 1,
        ]);

        $kamera->decrement('stok', 1);

        $pinjamAktif = Peminjaman::create([
            'user_id' => $peminjam->id,
            'nomor_peminjaman' => 'P-ACTIVE-002',
            'tanggal_pinjam' => now()->subDays(1),
            'tanggal_kembali_rencana' => now()->addDays(2),
            'status' => PeminjamanStatus::Disetujui,
            'keperluan' => 'Dokumentasi Event Kampus',
            'approved_by' => $petugas->id,
            'approved_at' => now()->subDays(1),
        ]);

        PeminjamanAlat::create([
            'peminjaman_id' => $pinjamAktif->id,
            'alat_id' => $kamera->id,
            'jumlah' => 1,
        ]);
    }
}