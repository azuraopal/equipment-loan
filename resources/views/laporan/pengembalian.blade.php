<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pengembalian</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: #111;
            font-size: 11px;
        }

        .header {
            padding: 24px 30px 16px;
            border-bottom: 2px solid #111;
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
            float: right;
            text-align: right;
            font-size: 9px;
            color: #888;
            margin-top: -30px;
        }

        .stats {
            display: flex;
            gap: 24px;
            padding: 12px 30px;
            background: #f9f9f9;
            border-bottom: 1px solid #e0e0e0;
        }

        .stat .num {
            font-size: 16px;
            font-weight: 700;
        }

        .stat .lbl {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 16px 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #222;
            color: #fff;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #eee;
            color: #333;
            border: 1px solid #ccc;
        }

        .badge-danger {
            background: #f5f5f5;
            color: #888;
            border: 1px solid #ddd;
        }

        .money {
            font-family: 'Courier New', monospace;
        }

        .detail-list {
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
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Pengembalian</h1>
        <div class="sub">Equipment Loan Management System</div>
        <div class="meta">
            Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
            Oleh: {{ auth()->user()->name }}
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="num">{{ $stats['total'] }}</div>
            <div class="lbl">Total</div>
        </div>
        <div class="stat">
            <div class="num">{{ $stats['lunas'] }}</div>
            <div class="lbl">Lunas</div>
        </div>
        <div class="stat">
            <div class="num">{{ $stats['belum_lunas'] }}</div>
            <div class="lbl">Belum Lunas</div>
        </div>
        <div class="stat">
            <div class="num">Rp{{ number_format($stats['total_denda'], 0, ',', '.') }}</div>
            <div class="lbl">Total Denda</div>
        </div>
    </div>

    <div class="content">
        <table>
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th style="width:13%">No. Pengembalian</th>
                    <th style="width:12%">Peminjam</th>
                    <th style="width:10%">Tgl Kembali</th>
                    <th style="width:18%">Kondisi Barang</th>
                    <th style="width:10%">Denda Telat</th>
                    <th style="width:10%">Denda Item</th>
                    <th style="width:10%">Total Denda</th>
                    <th style="width:8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $p->nomor_pengembalian }}</strong></td>
                        <td>{{ $p->peminjaman?->user?->name ?? '-' }}</td>
                        <td>{{ $p->tanggal_kembali_real?->format('d/m/Y') ?? '-' }}</td>
                        <td class="detail-list">
                            @foreach($p->details as $d)
                                {{ $d->alat->nama_alat ?? '-' }} — {{ $d->kondisi_kembali ?? 'Baik' }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                        <td class="money">Rp{{ number_format($p->denda_keterlambatan ?? 0, 0, ',', '.') }}</td>
                        <td class="money">Rp{{ number_format($p->details->sum('denda_item'), 0, ',', '.') }}</td>
                        <td class="money" style="{{ $p->total_denda > 0 ? 'font-weight:700;' : '' }}">
                            Rp{{ number_format($p->total_denda ?? 0, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge {{ $p->status_pembayaran === 'Lunas' ? 'badge-success' : 'badge-danger' }}">
                                {{ $p->status_pembayaran === 'Lunas' ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px; color:#aaa;">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <span>Equipment Loan Management System</span>
        <span>Halaman 1</span>
    </div>
</body>

</html>