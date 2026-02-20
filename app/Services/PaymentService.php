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
        /** @var Payment|null $pendingPayment */
        $pendingPayment = $pengembalian->payments()->where('status', 'pending')->latest()->first();

        if ($pendingPayment) {
            try {
                /** @var object $status */
                $status = Transaction::status($pendingPayment->order_id);


                if (in_array($status->transaction_status, ['expire', 'cancel', 'deny', 'failure'])) {
                    $pendingPayment->update(['status' => 'expired']);
                    $pendingPayment = null;
                } elseif ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                    $pendingPayment->update([
                        'status' => 'success',
                        'payment_type' => $status->payment_type ?? null,
                        'transaction_time' => $status->transaction_time ?? null,
                    ]);
                    $pengembalian->update([
                        'status_pembayaran' => 'Lunas',
                        'tanggal_bayar' => now(),
                    ]);
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
            \Log::error('PaymentService::createPayment failed', [
                'pengembalian_id' => $pengembalian->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Gagal membuat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Create a cash payment record (pending verification by petugas/admin).
     */
    public function createCashPayment(Pengembalian $pengembalian): Payment
    {
        // Cancel any existing pending midtrans payments
        $pengembalian->payments()->where('status', 'pending')->update(['status' => 'cancelled']);

        $orderId = 'CASH-' . $pengembalian->id . '-' . time();
        $amount = $pengembalian->total_denda;

        return Payment::create([
            'pengembalian_id' => $pengembalian->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_type' => 'cash',
            'status' => 'pending_verification',
        ]);
    }

    /**
     * Confirm a cash payment (called by petugas/admin).
     */
    public function confirmCashPayment(Payment $payment): void
    {
        $payment->update([
            'status' => 'success',
            'transaction_time' => now(),
        ]);

        $payment->pengembalian->update([
            'status_pembayaran' => 'Lunas',
            'tanggal_bayar' => now(),
        ]);
    }
}
