<?php

namespace App\Observers;

use App\Enums\PeminjamanStatus;
use App\Enums\UserRole;
use App\Models\Peminjaman;
use App\Models\User;
use Filament\Notifications\Notification;

class PeminjamanObserver
{
    public function created(Peminjaman $peminjaman): void
    {
        $peminjam = $peminjaman->user;

        $staffUsers = User::whereIn('role', [UserRole::Admin, UserRole::Petugas])->get();

        Notification::make()
            ->title('Peminjaman Baru')
            ->icon('heroicon-o-inbox-arrow-down')
            ->body("{$peminjam->name} mengajukan peminjaman — {$peminjaman->nomor_peminjaman}")
            ->info()
            ->sendToDatabase($staffUsers);
    }

    public function updated(Peminjaman $peminjaman): void
    {
        if (!$peminjaman->wasChanged('status')) {
            return;
        }

        $peminjam = $peminjaman->user;
        $staffUsers = User::whereIn('role', [UserRole::Admin, UserRole::Petugas])->get();

        match ($peminjaman->status) {
            PeminjamanStatus::Disetujui => Notification::make()
                ->title('Peminjaman Disetujui')
                ->icon('heroicon-o-check-circle')
                ->body("Peminjaman {$peminjaman->nomor_peminjaman} telah disetujui")
                ->success()
                ->sendToDatabase($peminjam),

            PeminjamanStatus::Ditolak => Notification::make()
                ->title('Peminjaman Ditolak')
                ->icon('heroicon-o-x-circle')
                ->body("Peminjaman {$peminjaman->nomor_peminjaman} ditolak")
                ->danger()
                ->sendToDatabase($peminjam),

            PeminjamanStatus::Menunggu_Verifikasi_Kembali => Notification::make()
                ->title('Pengajuan Pengembalian')
                ->icon('heroicon-o-arrow-uturn-left')
                ->body("{$peminjam->name} mengajukan pengembalian — {$peminjaman->nomor_peminjaman}")
                ->warning()
                ->sendToDatabase($staffUsers),

            PeminjamanStatus::Kembali => Notification::make()
                ->title('Pengembalian Selesai')
                ->icon('heroicon-o-check-badge')
                ->body("Peminjaman {$peminjaman->nomor_peminjaman} telah dikembalikan")
                ->success()
                ->sendToDatabase($peminjam),

            default => null,
        };
    }
}
