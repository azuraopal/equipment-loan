<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckUnpaidFines;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use App\Enums\StatusPembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnpaidFinesMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_when_user_has_unpaid_fines()
    {
        $user = User::factory()->create();
        $peminjaman = Peminjaman::factory()->create(['user_id' => $user->id]);

        $status = StatusPembayaran::tryFrom('Belum_Lunas') ?? 'Belum_Lunas';

        Pengembalian::factory()->create([
            'peminjaman_id' => $peminjaman->id,
            'status_pembayaran' => $status,
            'total_denda' => 50000,
            'petugas_id' => 1
        ]);

        $this->actingAs($user);

        Route::middleware(['web', CheckUnpaidFines::class])->get('/test-fines', function () {
            return 'OK';
        });

        $response = $this->get('/test-fines');

        $response->assertSessionHas('filament.notifications');

        $notifications = session('filament.notifications');
        $this->assertNotEmpty($notifications);
        $this->assertStringContainsString('Peringatan Denda', json_encode($notifications));
    }

    public function test_no_notification_when_user_has_no_fines()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Route::middleware(['web', CheckUnpaidFines::class])->get('/test-no-fines', function () {
            return 'OK';
        });

        $response = $this->get('/test-no-fines');

        $response->assertSessionMissing('filament.notifications');
    }
}
