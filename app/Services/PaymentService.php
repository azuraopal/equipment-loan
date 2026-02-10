<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Pengembalian;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function createPayment(Pengembalian $pengembalian)
    {
        // Check if there is already a pending payment
        $pendingPayment = $pengembalian->payments()->where('status', 'pending')->first();
        if ($pendingPayment) {
            return $pendingPayment->snap_token;
        }

        $orderId = 'PAY-' . $pengembalian->id . '-' . time();
        $amount = $pengembalian->total_denda;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $pengembalian->peminjaman->user->name,
                'email' => $pengembalian->peminjaman->user->email,
            ],
            'enabled_payments' => ['other_qris'],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            Payment::create([
                'pengembalian_id' => $pengembalian->id,
                'order_id' => $orderId,
                'amount' => $amount,
                'snap_token' => $snapToken,
                'status' => 'pending',
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Gagal membuat pembayaran: ' . $e->getMessage());
        }
    }
}
