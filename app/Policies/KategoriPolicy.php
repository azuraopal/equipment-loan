<?php

namespace App\Policies;

use App\Models\Kategori;
use App\Models\User;
use App\Enums\UserRole;

class KategoriPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Kategori $kategori): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Kategori $kategori): bool
    {
        return $user->role === UserRole::Admin;
    }
}