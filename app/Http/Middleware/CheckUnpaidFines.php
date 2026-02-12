<?php

namespace App\Http\Middleware;

use App\Services\DendaService;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUnpaidFines
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && DendaService::cekDendaBelumLunas(Auth::id())) {
            Notification::make()
                ->title('Peringatan Denda')
                ->body('Anda memiliki denda yang belum lunas. Harap segera melakukan pembayaran.')
                ->danger()
                ->persistent()
                ->send();
        }

        return $next($request);
    }
}
