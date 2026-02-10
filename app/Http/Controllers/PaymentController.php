<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function notification(Request $request)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            $payment = Payment::where('order_id', $orderId)->firstOrFail();

            if ($transactionStatus == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $payment->status = 'challenge';
                    } else {
                        $payment->status = 'success';
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $payment->status = 'success';
            } elseif ($transactionStatus == 'pending') {
                $payment->status = 'pending';
            } elseif ($transactionStatus == 'deny') {
                $payment->status = 'failed';
            } elseif ($transactionStatus == 'expire') {
                $payment->status = 'expired';
            } elseif ($transactionStatus == 'cancel') {
                $payment->status = 'cancelled';
            }

            $payment->payment_type = $type;
            $payment->transaction_time = $notification->transaction_time;
            $payment->payload = json_encode($notification->getResponse());
            $payment->save();

            if ($payment->status == 'success') {
                $pengembalian = $payment->pengembalian;
                $pengembalian->update([
                    'status_pembayaran' => 'Lunas',
                    'tanggal_bayar' => now(),
                    'bukti_bayar' => 'midtrans-' . $orderId,
                ]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
