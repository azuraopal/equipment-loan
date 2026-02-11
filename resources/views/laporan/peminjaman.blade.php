<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: #111;
            font-size: 12px;
        }

        .header {
            padding: 24px 30px 16px;
            border-bottom: 2px solid #111;
            position: relative;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .header .sub {
            font-size: 10px;
            color: #666;
        }

        .header .meta {
            position: absolute;
            top: 24px;
            right: 30px;
            text-align: right;
            font-size: 9px;
            color: #888;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
            border-bottom: 1px solid #e0e0e0;
        }

        .stats-table td {
            padding: 12px 16px;
            text-align: center;
            border: none;
        }

        .stats-table .num {
            font-size: 16px;
            font-weight: 700;
            color: #111;
        }

        .stats-table .lbl {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 16px 30px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data thead th {
            background: #222;
            color: #fff;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        table.data tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        table.data tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-warning {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
        }

        .badge-success {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ccc;
        }

        .badge-danger {
            background: #f5f5f5;
            color: #999;
            border: 1px solid #ddd;
            text-decoration: line-through;
        }

        .badge-info {
            background: #eee;
            color: #444;
            border: 1px solid #ccc;
        }

        .badge-gray {
            background: #f5f5f5;
            color: #888;
            border: 1px solid #ddd;
        }

        .alat-list {
            font-size: 9px;
            color: #555;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ddd;
            padding: 6px 30px;
            font-size: 8px;
            color: #aaa;
        }

        .footer table {
            width: 100%;
        }

        .footer td {
            border: none;
            padding: 0;
        }

        .footer td:last-child {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Peminjaman</h1>
        <div class="sub">Equipment Loan Management System</div>
        <div class="meta">
            Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
            Oleh: {{ auth()->user()->name }}
            @if($periode['dari_label'] || $periode['sampai_label'])
                <br>Periode: {{ $periode['dari_label'] ?? '...' }} — {{ $periode['sampai_label'] ?? '...' }}
            @endif
        </div>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="num">{{ $stats['total'] }}</div>
                <div class="lbl">Total</div>
            </td>
            <td>
                <div class="num">{{ $stats['menunggu'] }}</div>
                <div class="lbl">Menunggu</div>
            </td>
            <td>
                <div class="num">{{ $stats['disetujui'] }}</div>
                <div class="lbl">Dipinjam</div>
            </td>
            <td>
                <div class="num">{{ $stats['kembali'] }}</div>
                <div class="lbl">Kembali</div>
            </td>
            <td>
                <div class="num">{{ $stats['ditolak'] }}</div>
                <div class="lbl">Ditolak</div>
            </td>
        </tr>
    </table>

    <div class="content">
        <table class="data">
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th style="width:14%">No. Peminjaman</th>
                    <th style="width:14%">Peminjam</th>
                    <th style="width:22%">Alat</th>
                    <th style="width:10%">Tgl Pinjam</th>
                    <th style="width:10%">Rencana Kembali</th>
                    <th style="width:14%">Keperluan</th>
                    <th style="width:10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $p->nomor_peminjaman }}</strong></td>
                        <td>{{ $p->user->name ?? '-' }}</td>
                        <td class="alat-list">
                            @foreach($p->peminjamanDetails as $d)
                                {{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }})@if(!$loop->last), @endif
                            @endforeach
                        </td>
                        <td>{{ $p->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $p->tanggal_kembali_rencana?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($p->keperluan, 30) }}</td>
                        <td>
                            @php
                                $badgeClass = match ($p->status) {
                                    \App\Enums\PeminjamanStatus::Menunggu => 'badge-warning',
                                    \App\Enums\PeminjamanStatus::Disetujui => 'badge-success',
                                    \App\Enums\PeminjamanStatus::Ditolak => 'badge-danger',
                                    \App\Enums\PeminjamanStatus::Kembali => 'badge-info',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $p->status->getLabel() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:20px; color:#aaa;">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>Equipment Loan Management System</td>
                <td>Halaman 1</td>
            </tr>
        </table>
    </div>
</body>

</html>