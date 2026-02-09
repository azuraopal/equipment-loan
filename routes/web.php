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
