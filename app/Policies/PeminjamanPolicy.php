<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;
use App\Enums\UserRole;

class PeminjamanPolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->role === UserRole::Peminjam) {
            return $user->id === $peminjaman->user_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Peminjam]);
    }

    public function update(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === UserRole::Admin;
    }
}