<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckUnpaidFines;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Peminjam\Resources\Pengembalian\PengembalianResource;

class PeminjamPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('peminjam')
            ->path('peminjam')
            ->authGuard('web')
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Green,
            ])
            ->resources([
                PengembalianResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Peminjam/Resources'), for: 'App\Filament\Peminjam\Resources')
            ->discoverPages(in: app_path('Filament/Peminjam/Pages'), for: 'App\Filament\Peminjam\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Peminjam/Widgets'), for: 'App\Filament\Peminjam\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckUnpaidFines::class,
            ]);
    }
}