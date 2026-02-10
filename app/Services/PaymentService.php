<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Pengembalian;
use Midtrans\Config;
use Midtrans\Snap;

use Midtrans\Transaction;

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
        $pendingPayment = $pengembalian->payments()->where('status', 'pending')->latest()->first();

        if ($pendingPayment) {
            try {
                $status = Transaction::status($pendingPayment->order_id);

                if (in_array($status->transaction_status, ['expire', 'cancel', 'deny', 'failure'])) {
                    $pendingPayment->update(['status' => 'expired']);
                    $pendingPayment = null;
                } elseif ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                    $pendingPayment->update(['status' => 'success']);
                    $pengembalian->update(['status_pembayaran' => 'Lunas']);
                    return null;
                } else {
                    return $pendingPayment->snap_token;
                }
            } catch (\Exception $e) {
                return $pendingPayment->snap_token;
            }
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
