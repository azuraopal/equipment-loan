<?php

use App\Enums\UserRole;
use App\Http\Controllers\AlatInfoController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public QR Info Pages (no auth required)
Route::get('/alat/info/{kode_alat}', [AlatInfoController::class, 'alat'])->name('alat.info');
Route::get('/peminjaman/info/{nomor}', [AlatInfoController::class, 'peminjaman'])->name('peminjaman.info');

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::Admin => redirect('/admin'),
            UserRole::Petugas => redirect('/petugas'),
            UserRole::Peminjam => redirect('/peminjam'),
            default => redirect('/admin/login'),
        };
    }

    return redirect('/admin/login');
});

Route::get('/login', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::Admin => redirect('/admin'),
            UserRole::Petugas => redirect('/petugas'),
            UserRole::Peminjam => redirect('/peminjam'),
            default => redirect('/admin/login'),
        };
    }

    return redirect('/admin/login');
})->name('login');

Route::middleware(['auth'])->prefix('admin/laporan')->group(function () {
    Route::get('/peminjaman', [LaporanController::class, 'peminjaman'])->name('laporan.peminjaman');
    Route::get('/pengembalian', [LaporanController::class, 'pengembalian'])->name('laporan.pengembalian');
    Route::get('/inventaris', [LaporanController::class, 'inventaris'])->name('laporan.inventaris');
});

Route::middleware(['auth'])->get('/payment/{pengembalian}', [PaymentController::class, 'showPayment'])->name('payment.show');
