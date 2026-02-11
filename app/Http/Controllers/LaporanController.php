<?php

namespace App\Http\Controllers;

use App\Enums\PeminjamanStatus;
use App\Enums\UserRole;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    private function authorize(): void
    {
        $role = auth()->user()?->role;
        abort_unless(
            $role === UserRole::Petugas,
            403,
            'Hanya Petugas yang dapat mengakses laporan.'
        );
    }

    private function parsePeriode(Request $request): array
    {
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        return [
            'dari' => $dari ? Carbon::parse($dari)->startOfDay() : null,
            'sampai' => $sampai ? Carbon::parse($sampai)->endOfDay() : null,
            'dari_label' => $dari ? Carbon::parse($dari)->format('d/m/Y') : null,
            'sampai_label' => $sampai ? Carbon::parse($sampai)->format('d/m/Y') : null,
        ];
    }

    public function peminjaman(Request $request)
    {
        $this->authorize();

        $periode = $this->parsePeriode($request);

        $query = Peminjaman::with(['user', 'peminjamanDetails.alat'])->latest();

        if ($periode['dari']) {
            $query->where('tanggal_pinjam', '>=', $periode['dari']);
        }
        if ($periode['sampai']) {
            $query->where('tanggal_pinjam', '<=', $periode['sampai']);
        }

        $data = $query->get();

        $stats = [
            'total' => $data->count(),
            'menunggu' => $data->where('status', PeminjamanStatus::Menunggu)->count(),
            'disetujui' => $data->where('status', PeminjamanStatus::Disetujui)->count(),
            'kembali' => $data->where('status', PeminjamanStatus::Kembali)->count(),
            'ditolak' => $data->where('status', PeminjamanStatus::Ditolak)->count(),
        ];

        $pdf = Pdf::loadView('laporan.peminjaman', compact('data', 'stats', 'periode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf');
    }

    public function pengembalian(Request $request)
    {
        $this->authorize();

        $periode = $this->parsePeriode($request);

        $query = Pengembalian::with(['peminjaman.user', 'details.alat', 'petugas'])->latest();

        if ($periode['dari']) {
            $query->where('tanggal_kembali_real', '>=', $periode['dari']);
        }
        if ($periode['sampai']) {
            $query->where('tanggal_kembali_real', '<=', $periode['sampai']);
        }

        $data = $query->get();

        $stats = [
            'total' => $data->count(),
            'lunas' => $data->where('status_pembayaran', 'Lunas')->count(),
            'belum_lunas' => $data->where('status_pembayaran', 'Belum_Lunas')->count(),
            'total_denda' => $data->sum('total_denda'),
        ];

        $pdf = Pdf::loadView('laporan.pengembalian', compact('data', 'stats', 'periode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pengembalian-' . now()->format('Y-m-d') . '.pdf');
    }

    public function inventaris(Request $request)
    {
        $this->authorize();

        $periode = $this->parsePeriode($request);

        $data = Alat::with('kategori')->orderBy('nama_alat')->get();

        $stats = [
            'total_alat' => $data->count(),
            'total_stok' => $data->sum('stok'),
            'total_nilai' => $data->sum(fn($a) => $a->stok * $a->harga_satuan),
        ];

        $pdf = Pdf::loadView('laporan.inventaris', compact('data', 'stats', 'periode'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-inventaris-' . now()->format('Y-m-d') . '.pdf');
    }
}
