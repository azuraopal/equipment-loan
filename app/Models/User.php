<?php

namespace App\Models;

use App\Services\DendaService;
use App\Traits\MencatatAktivitas;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property UserRole $role
 * @property string $email
 * @property string $name
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MencatatAktivitas;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === UserRole::Admin,
            'petugas' => $this->role === UserRole::Petugas,
            'peminjam' => $this->role === UserRole::Peminjam,
            default => false,
        };
    }

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->peminjamans()->exists()) {
                Notification::make()
                    ->danger()
                    ->title('Gagal Menghapus')
                    ->body('Tidak dapat menghapus user karena masih memiliki riwayat peminjaman.')
                    ->send();
                return false;
            }

            if ($user->hasDendaBelumLunas()) {
                Notification::make()
                    ->danger()
                    ->title('Gagal Menghapus')
                    ->body('Tidak dapat menghapus user karena masih memiliki denda yang belum lunas.')
                    ->send();
                return false;
            }
        });
    }

    public function hasDendaBelumLunas(): bool
    {
        return DendaService::cekDendaBelumLunas($this->id);
    }
}
