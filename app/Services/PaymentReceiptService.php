<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Midtrans\Config;
use Midtrans\Transaction;

class PaymentReceiptService
{
    public static function getPayment(Pengembalian $record): ?Payment
    {
        /** @var Payment|null $payment */
        $payment = $record->payments()->where('status', 'success')->latest()->first()
            ?? $record->payments()->latest()->first();

        if (!$payment) {
            return null;
        }

        if ($payment->order_id && !$payment->payment_type) {
            try {
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production');
                Config::$isSanitized = config('services.midtrans.is_sanitized');
                Config::$is3ds = config('services.midtrans.is_3ds');

                /** @var object $status */
                $status = Transaction::status($payment->order_id);

                $payment->update([
                    'payment_type' => $status->payment_type ?? null,
                    'transaction_time' => $status->transaction_time ?? null,
                    'status' => match ($status->transaction_status ?? '') {
                        'settlement', 'capture' => 'success',
                        'expire' => 'expired',
                        'cancel' => 'cancelled',
                        'deny' => 'failed',
                        default => $payment->status,
                    },
                ]);

                $payment = $payment->fresh();
            } catch (\Exception $e) {
                \Log::debug('Failed to fetch Midtrans status: ' . $e->getMessage());
            }
        }

        return $payment;
    }

    public static function formatPaymentType(?string $type): string
    {
        if (!$type)
            return 'Tidak diketahui';

        return match ($type) {
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'cstore' => 'Convenience Store',
            'echannel' => 'Mandiri Bill',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'cash' => 'Tunai (Cash)',
            default => strtoupper(str_replace('_', ' ', $type)),
        };
    }

    public static function renderReceipt(Pengembalian $record): HtmlString
    {
        $record->load('peminjaman.user', 'petugas', 'details.alat');
        $payment = self::getPayment($record);

        $html = '<div style="max-width:480px; margin:0 auto; font-family:\'Inter\',system-ui,sans-serif; font-size:14px;">';

        $html .= '<div style="text-align:center; padding:24px 20px; background:linear-gradient(135deg,#1e3a5f,#2563eb); border-radius:16px 16px 0 0;">';
        $html .= '<div style="font-size:18px; font-weight:700; color:#fff;">Struk Pembayaran</div>';
        $html .= '<div style="font-size:12px; color:rgba(255,255,255,0.6); margin-top:4px;">Equipment Loan System</div>';
        $html .= '</div>';

        $isPaid = $record->status_pembayaran === 'Lunas';
        $bannerBg = $isPaid ? 'rgba(34,197,94,0.08)' : 'rgba(250,204,21,0.08)';
        $bannerBorder = $isPaid ? 'rgba(34,197,94,0.2)' : 'rgba(250,204,21,0.2)';
        $bannerColor = $isPaid ? '#4ade80' : '#fbbf24';
        $bannerText = $isPaid ? 'LUNAS' : 'Belum Lunas';

        $html .= '<div style="text-align:center; padding:12px; background:' . $bannerBg . '; border:1px solid ' . $bannerBorder . '; margin:0;">';
        $html .= '<span style="font-weight:700; font-size:15px; color:' . $bannerColor . ';">' . $bannerText . '</span>';
        $html .= '</div>';

        $html .= '<div style="padding:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(148,163,184,0.1); border-top:none;">';

        $rows = [
            ['No. Pengembalian', $record->nomor_pengembalian],
            ['Peminjam', $record->peminjaman->user->name ?? '-'],
            ['Tgl Kembali', Carbon::parse($record->tanggal_kembali_real)->format('d M Y')],
            ['Petugas', $record->petugas->name ?? '-'],
        ];

        foreach ($rows as $row) {
            $html .= '<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed rgba(148,163,184,0.15);">';
            $html .= '<span style="color:#94a3b8; font-size:13px;">' . $row[0] . '</span>';
            $html .= '<span style="font-weight:600; font-size:13px;">' . e($row[1]) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<div style="padding:16px 20px; background:rgba(255,255,255,0.03); border:1px solid rgba(148,163,184,0.1); border-top:none;">';
        $html .= '<div style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Barang Dikembalikan</div>';

        foreach ($record->details as $detail) {
            $kondisiColor = match ($detail->kondisi_kembali) {
                'Baik' => '#22c55e', 'Rusak' => '#eab308', 'Hilang' => '#ef4444', default => '#9ca3af'
            };
            $html .= '<div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">';
            $html .= '<div><span style="font-weight:500;">' . e($detail->alat->nama_alat) . '</span> <span style="color:#64748b; font-size:12px;">×' . $detail->jumlah_kembali . '</span></div>';
            $html .= '<span style="font-size:11px; color:' . $kondisiColor . '; font-weight:600; border:1px solid ' . $kondisiColor . '; padding:2px 8px; border-radius:10px;">' . $detail->kondisi_kembali . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<div style="padding:16px 20px; background:rgba(255,255,255,0.02); border:1px solid rgba(148,163,184,0.1); border-top:none;">';
        $html .= '<div style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Rincian Denda</div>';

        $dendaRows = [
            ['Keterlambatan' . ($record->hari_terlambat > 0 ? ' (' . $record->hari_terlambat . ' hari)' : ''), $record->denda_keterlambatan],
            ['Kerusakan', $record->denda_kerusakan],
            ['Kehilangan', $record->denda_kehilangan],
        ];
        foreach ($dendaRows as $d) {
            if ($d[1] > 0) {
                $html .= '<div style="display:flex; justify-content:space-between; padding:4px 0;">';
                $html .= '<span style="color:#94a3b8; font-size:13px;">' . $d[0] . '</span>';
                $html .= '<span style="color:#f9a8a8; font-size:13px;">Rp ' . number_format((float) $d[1], 0, ',', '.') . '</span>';
                $html .= '</div>';
            }
        }

        $html .= '<div style="display:flex; justify-content:space-between; padding:10px 0 4px; border-top:2px solid rgba(148,163,184,0.2); margin-top:8px;">';
        $html .= '<span style="font-weight:700; font-size:15px; color:#e2e8f0;">Total</span>';
        $html .= '<span style="font-weight:700; font-size:15px; color:#fca5a5;">Rp ' . number_format((float) $record->total_denda, 0, ',', '.') . '</span>';
        $html .= '</div>';
        $html .= '</div>';

        if ($payment && $payment->status === 'success') {
            $html .= '<div style="padding:16px 20px; background:rgba(255,255,255,0.02); border:1px solid rgba(148,163,184,0.1); border-top:none;">';
            $html .= '<div style="font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Info Pembayaran</div>';

            $paymentRows = [
                ['Metode', self::formatPaymentType($payment->payment_type)],
                ['Order ID', $payment->order_id],
                ['Jumlah', 'Rp ' . number_format((float) $payment->amount, 0, ',', '.')],
            ];

            if ($payment->transaction_time) {
                $paymentRows[] = ['Waktu Transaksi', Carbon::parse($payment->transaction_time)->format('d M Y, H:i:s')];
            }

            foreach ($paymentRows as $pr) {
                $html .= '<div style="display:flex; justify-content:space-between; padding:4px 0;">';
                $html .= '<span style="color:#94a3b8; font-size:13px;">' . $pr[0] . '</span>';
                $html .= '<span style="font-weight:500; font-size:13px;">' . e($pr[1]) . '</span>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } elseif ($payment && $payment->payment_type === 'cash' && $payment->status === 'pending_verification') {
            $html .= '<div style="padding:16px 20px; background:rgba(251,191,36,0.05); border:1px solid rgba(148,163,184,0.1); border-top:none;">';
            $html .= '<div style="text-align:center; color:#fbbf24; font-weight:600;">Menunggu verifikasi pembayaran cash</div>';
            $html .= '</div>';
        }

        $html .= '<div style="text-align:center; padding:16px; background:rgba(255,255,255,0.02); border:1px solid rgba(148,163,184,0.1); border-top:none; border-radius:0 0 16px 16px;">';
        $html .= '<div style="font-size:11px; color:#475569;">Dicetak pada ' . now()->format('d M Y, H:i') . '</div>';
        $html .= '<div style="font-size:11px; color:#475569; margin-top:2px;">Equipment Loan System</div>';
        $html .= '</div>';

        $html .= '</div>';

        return new HtmlString($html);
    }
}
