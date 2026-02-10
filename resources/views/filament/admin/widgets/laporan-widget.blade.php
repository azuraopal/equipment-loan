<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-document-arrow-down">
        <x-slot name="heading">
            Download Laporan
        </x-slot>
        <x-slot name="description">
            Cetak laporan dalam format PDF
        </x-slot>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            <a href="{{ route('laporan.peminjaman') }}"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03);">
                <x-heroicon-o-document-text style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Peminjaman</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar transaksi pinjam</div>
                </div>
            </a>

            <a href="{{ route('laporan.pengembalian') }}"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03);">
                <x-heroicon-o-document-check style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Pengembalian</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar transaksi & denda</div>
                </div>
            </a>

            <a href="{{ route('laporan.inventaris') }}"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03);">
                <x-heroicon-o-cube style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Inventaris</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar aset & stok</div>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>