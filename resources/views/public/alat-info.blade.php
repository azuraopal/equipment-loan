<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $alat->nama_alat }} — Info Alat</title>
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
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            padding: 32px 28px;
            text-align: center;
            position: relative;
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

        .badge-kode {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .card-header h1 {
            font-size: 22px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 8px 28px 28px;
        }

        .image-wrapper {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .image-wrapper img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
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
            font-size: 16px;
            font-weight: 600;
        }

        .info-value.success {
            color: #4ade80;
        }

        .info-value.warning {
            color: #fbbf24;
        }

        .info-value.danger {
            color: #f87171;
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

        .status-badge.available {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .status-badge.low {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-badge.empty {
            background: rgba(248, 113, 113, 0.15);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.3);
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
    <div class="card">
        <div class="card-header">
            <div class="badge-kode">{{ $alat->kode_alat }}</div>
            <h1>{{ $alat->nama_alat }}</h1>
        </div>
        <div class="card-body">
            @if($alat->gambar)
                <div class="image-wrapper">
                    <img src="{{ asset('storage/' . $alat->gambar) }}" alt="{{ $alat->nama_alat }}">
                </div>
            @endif

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Kategori</div>
                    <div class="info-value">{{ $alat->kategori->nama_kategori ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Kondisi</div>
                    <div class="info-value">{{ $alat->kondisi_awal }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Stok Tersedia</div>
                    <div
                        class="info-value {{ $alat->stok > 2 ? 'success' : ($alat->stok > 0 ? 'warning' : 'danger') }}">
                        {{ $alat->stok }} unit
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sedang Dipinjam</div>
                    <div class="info-value">{{ $sedangDipinjam }} peminjaman</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($alat->stok > 2)
                            <span class="status-badge available">✅ Tersedia</span>
                        @elseif($alat->stok > 0)
                            <span class="status-badge low">⚠️ Stok Terbatas</span>
                        @else
                            <span class="status-badge empty">❌ Habis</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Harga Satuan</div>
                    <div class="info-value">Rp {{ number_format($alat->harga_satuan, 0, ',', '.') }}</div>
                </div>

                @if($alat->spesifikasi)
                    <div class="info-item full">
                        <div class="info-label">Spesifikasi</div>
                        <div class="info-value" style="font-size:14px; font-weight:400; line-height:1.6;">
                            {{ $alat->spesifikasi }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="qr-section">
                {!! $qrCode !!}
                <div class="qr-label">Scan QR ini untuk melihat info alat</div>
            </div>
        </div>
        <div class="footer">
            Equipment Loan System &bull; {{ now()->format('d M Y, H:i') }}
        </div>
    </div>
</body>

</html>