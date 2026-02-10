<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        parent::register();
        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class
        );
    }

    public function boot(): void
    {
        \App\Models\Peminjaman::observe(\App\Observers\PeminjamanObserver::class);

        Filament::serving(function () {
            if (!Auth::check())
                return;

            $user = Auth::user();

            if (request()->is('admin/login')) {
                redirect(match ($user->role) {
                    'admin' => '/admin',
                    'petugas' => '/petugas',
                    'peminjam' => '/peminjam',
                    default => '/admin/login',
                })->send();
            }
        });
    }
}
