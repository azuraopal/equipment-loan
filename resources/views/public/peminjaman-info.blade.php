<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $peminjaman->nomor_peminjaman }} — Bukti Peminjaman</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 24px;
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            padding: 32px 28px;
            text-align: center;
            position: relative;
        }

        .card-header.approved {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .card-header.pending {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .card-header.rejected {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .card-header.returned {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 24px 24px 0 0;
        }

        .badge-nomor {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .card-header h1 {
            font-size: 20px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .card-header .subtitle {
            font-size: 13px;
            opacity: 0.85;
            margin-top: 4px;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 8px 28px 28px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            background: rgba(15, 23, 42, 0.5);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        .info-item.full {
            grid-column: 1 / -1;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .item-list {
            margin-bottom: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
            margin-bottom: 8px;
            border: 1px solid rgba(148, 163, 184, 0.06);
        }

        .item-name {
            font-weight: 500;
            font-size: 14px;
        }

        .item-qty {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-badge.success {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
        }

        .status-badge.warning {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }

        .status-badge.danger {
            background: rgba(248, 113, 113, 0.15);
            color: #f87171;
        }

        .status-badge.info {
            background: rgba(96, 165, 250, 0.15);
            color: #60a5fa;
        }

        .qr-section {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        .qr-section svg {
            max-width: 140px;
            height: auto;
        }

        .qr-label {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
        }

        .footer {
            text-align: center;
            padding: 16px;
            font-size: 11px;
            color: #475569;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
        }
    </style>
</head>

<body>
    @php
        $statusClass = match ($peminjaman->status) {
            \App\Enums\PeminjamanStatus::Disetujui => 'approved',
            \App\Enums\PeminjamanStatus::Menunggu => 'pending',
            \App\Enums\PeminjamanStatus::Ditolak => 'rejected',
            \App\Enums\PeminjamanStatus::Kembali => 'returned',
            default => 'pending',
        };
        $statusBadgeClass = match ($peminjaman->status) {
            \App\Enums\PeminjamanStatus::Disetujui => 'success',
            \App\Enums\PeminjamanStatus::Menunggu => 'warning',
            \App\Enums\PeminjamanStatus::Ditolak => 'danger',
            \App\Enums\PeminjamanStatus::Kembali => 'info',
            default => 'warning',
        };
    @endphp

    <div class="card">
        <div class="card-header {{ $statusClass }}">
            <div class="badge-nomor">{{ $peminjaman->nomor_peminjaman }}</div>
            <h1>Bukti Peminjaman Digital</h1>
            <div class="subtitle">Equipment Loan System</div>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Peminjam</div>
                    <div class="info-value">{{ $peminjaman->user->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge {{ $statusBadgeClass }}">
                            {{ $peminjaman->status->getLabel() }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Pinjam</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Rencana Kembali</div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali_rencana)->format('d M Y') }}</div>
                </div>

                @if($peminjaman->keperluan)
                    <div class="info-item full">
                        <div class="info-label">Keperluan</div>
                        <div class="info-value" style="font-size:13px; font-weight:400; line-height:1.6;">
                            {{ $peminjaman->keperluan }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="item-list">
                <div class="section-title">Daftar Barang Dipinjam</div>
                @foreach($peminjaman->peminjamanDetails as $detail)
                    <div class="item-row">
                        <span class="item-name">{{ $detail->alat->nama_alat }}</span>
                        <span class="item-qty">{{ $detail->jumlah }} unit</span>
                    </div>
                @endforeach
            </div>

            @if($peminjaman->pengembalian)
                <div class="info-grid" style="margin-bottom: 20px;">
                    <div class="info-item full">
                        <div class="info-label">Tanggal Dikembalikan</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($peminjaman->pengembalian->tanggal_kembali_real)->format('d M Y') }}
                            @if($peminjaman->pengembalian->total_denda > 0)
                                — Denda: Rp {{ number_format($peminjaman->pengembalian->total_denda, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="qr-section">
                {!! $qrCode !!}
                <div class="qr-label">Scan QR ini untuk verifikasi peminjaman</div>
            </div>
        </div>
        <div class="footer">
            Equipment Loan System &bull; Dibuat: {{ $peminjaman->created_at->format('d M Y, H:i') }}
        </div>
    </div>
</body>

</html>