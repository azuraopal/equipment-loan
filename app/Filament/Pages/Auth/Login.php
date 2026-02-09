<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Auth\SessionGuard;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);

        $user = $authProvider->retrieveByCredentials($credentials);

        if ((!$user) || (!$authProvider->validateCredentials($user, $credentials))) {
            $this->userUndertakingMultiFactorAuthentication = null;
            $this->throwFailureValidationException();
        }

        if (!$user instanceof User) {
            $this->throwFailureValidationException();
        }

        $validRoles = [UserRole::Admin, UserRole::Petugas, UserRole::Peminjam];

        if (!in_array($user->role, $validRoles)) {
            $this->throwFailureValidationException();
        }

        if (!$authGuard->attempt($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
