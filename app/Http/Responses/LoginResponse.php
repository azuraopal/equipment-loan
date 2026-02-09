<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

use Filament\Auth\Http\Responses\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        if ($user) {
            if ($user->role === UserRole::Admin) {
                return redirect()->to('/admin');
            }

            if ($user->role === UserRole::Petugas) {
                return redirect()->to('/petugas');
            }

            if ($user->role === UserRole::Peminjam) {
                return redirect()->to('/peminjam');
            }
        }

        return parent::toResponse($request);
    }
}