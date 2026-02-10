<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
    Route::get('/peminjaman', [\App\Http\Controllers\LaporanController::class, 'peminjaman'])->name('laporan.peminjaman');
    Route::get('/pengembalian', [\App\Http\Controllers\LaporanController::class, 'pengembalian'])->name('laporan.pengembalian');
    Route::get('/inventaris', [\App\Http\Controllers\LaporanController::class, 'inventaris'])->name('laporan.inventaris');
});

Route::middleware(['auth'])->get('/payment/{pengembalian}', [\App\Http\Controllers\PaymentController::class, 'showPayment'])->name('payment.show');
