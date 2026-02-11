<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Inventaris Alat</title>
    <style>
        @page {
            size: A4 portrait;
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

        .money {
            font-family: 'Courier New', monospace;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .total-row {
            background: #f5f5f5 !important;
        }

        .total-row td {
            border-top: 2px solid #333;
            padding: 10px 6px;
            font-weight: 700;
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
        <h1>Laporan Inventaris Alat</h1>
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
                <div class="num">{{ $stats['total_alat'] }}</div>
                <div class="lbl">Jenis Alat</div>
            </td>
            <td>
                <div class="num">{{ $stats['total_stok'] }}</div>
                <div class="lbl">Total Unit</div>
            </td>
            <td>
                <div class="num">Rp{{ number_format($stats['total_nilai'], 0, ',', '.') }}</div>
                <div class="lbl">Nilai Inventaris</div>
            </td>
        </tr>
    </table>

    <div class="content">
        <table class="data">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:12%">Kode Alat</th>
                    <th style="width:25%">Nama Alat</th>
                    <th style="width:15%">Kategori</th>
                    <th style="width:8%">Stok</th>
                    <th style="width:12%">Harga Satuan</th>
                    <th style="width:13%">Total Nilai</th>
                    <th style="width:10%">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $a->kode_alat }}</strong></td>
                        <td>{{ $a->nama_alat }}</td>
                        <td>{{ $a->kategori->nama_kategori ?? '-' }}</td>
                        <td class="text-center {{ $a->stok <= 0 ? 'bold' : '' }}">{{ $a->stok }}</td>
                        <td class="money">Rp{{ number_format($a->harga_satuan, 0, ',', '.') }}</td>
                        <td class="money">Rp{{ number_format($a->stok * $a->harga_satuan, 0, ',', '.') }}</td>
                        <td>{{ $a->kondisi_awal ?? 'Baik' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:20px; color:#aaa;">Tidak ada data</td>
                    </tr>
                @endforelse

                @if($data->count() > 0)
                    <tr class="total-row">
                        <td colspan="4" class="text-right">TOTAL</td>
                        <td class="text-center">{{ $stats['total_stok'] }}</td>
                        <td></td>
                        <td class="money">Rp{{ number_format($stats['total_nilai'], 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
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