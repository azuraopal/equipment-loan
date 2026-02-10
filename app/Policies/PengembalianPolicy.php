<?php

namespace App\Policies;

use App\Models\Pengembalian;
use App\Models\User;
use App\Enums\UserRole;

class PengembalianPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Petugas, UserRole::Peminjam]);
    }

    public function view(User $user, Pengembalian $pengembalian): bool
    {   
        if ($user->role === UserRole::Peminjam) {
            return $user->id === $pengembalian->peminjaman->user_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }
}