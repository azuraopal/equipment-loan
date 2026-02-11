<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-document-arrow-down">
        <x-slot name="heading">
            Download Laporan
        </x-slot>
        <x-slot name="description">
            Cetak laporan dalam format PDF berdasarkan periode
        </x-slot>

        <div style="display: flex; gap: 12px; margin-bottom: 16px; align-items: flex-end;">
            <div style="flex: 1;">
                <label
                    style="display: block; font-size: 12px; font-weight: 500; color: #a8a29e; margin-bottom: 4px;">Dari
                    Tanggal</label>
                <input type="date" wire:model.live="dari"
                    style="width: 100%; padding: 8px 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; background: rgba(255,255,255,0.05); color: #e7e5e4; font-size: 13px; outline: none;" />
            </div>
            <div style="flex: 1;">
                <label
                    style="display: block; font-size: 12px; font-weight: 500; color: #a8a29e; margin-bottom: 4px;">Sampai
                    Tanggal</label>
                <input type="date" wire:model.live="sampai"
                    style="width: 100%; padding: 8px 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; background: rgba(255,255,255,0.05); color: #e7e5e4; font-size: 13px; outline: none;" />
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            <a href="{{ $this->buildUrl('laporan.peminjaman') }}" target="_blank"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03); transition: background 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                <x-heroicon-o-document-text style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Peminjaman</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar transaksi pinjam</div>
                </div>
            </a>

            <a href="{{ $this->buildUrl('laporan.pengembalian') }}" target="_blank"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03); transition: background 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                <x-heroicon-o-document-check style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Pengembalian</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar transaksi & denda</div>
                </div>
            </a>

            <a href="{{ $this->buildUrl('laporan.inventaris') }}" target="_blank"
                style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; text-decoration: none; background: rgba(255,255,255,0.03); transition: background 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                <x-heroicon-o-cube style="width: 20px; height: 20px; color: #a8a29e; flex-shrink: 0;" />
                <div>
                    <div style="font-weight: 600; color: #e7e5e4; font-size: 13px;">Laporan Inventaris</div>
                    <div style="font-size: 11px; color: #78716c;">Daftar aset & stok</div>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>