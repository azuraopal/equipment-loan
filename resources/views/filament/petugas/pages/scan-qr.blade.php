<x-filament-panels::page>
    <div x-data="qrScanner()" x-init="init()" class="space-y-6">
        {{-- Scanner Section --}}
        <div
            class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4">
                <div class="flex-1">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-camera class="w-5 h-5 text-primary-500" />
                        Scanner Kamera
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Arahkan kamera ke QR Code alat atau surat peminjaman
                    </p>
                </div>
                <button @click="toggleScanner()"
                    :class="scanning ? 'bg-danger-600 hover:bg-danger-700' : 'bg-primary-600 hover:bg-primary-700'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold transition-all duration-200 shadow-sm">
                    <template x-if="!scanning">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9V5a2 2 0 012-2h4M15 3h4a2 2 0 012 2v4M21 15v4a2 2 0 01-2 2h-4M9 21H5a2 2 0 01-2-2v-4" />
                            </svg>
                            Mulai Scan
                        </span>
                    </template>
                    <template x-if="scanning">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Stop Scan
                        </span>
                    </template>
                </button>
            </div>

            <div class="px-6 pb-6">
                <div id="qr-reader" x-show="scanning" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="rounded-xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600"
                    style="max-width: 500px; margin: 0 auto;"></div>

                <div x-show="!scanning" class="text-center py-12">
                    <div
                        class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Klik <strong>"Mulai Scan"</strong> untuk
                        mengaktifkan kamera</p>
                </div>
            </div>
        </div>

        {{-- Results Section --}}
        @if($alatInfo)
            <div
                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 animate-in slide-in-from-bottom-4 duration-500">
                <div
                    class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2 flex-1">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-success-50 dark:bg-success-500/10">
                            <x-heroicon-o-cube class="w-4 h-4 text-success-600 dark:text-success-400" />
                        </span>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Info Alat Ditemukan</h3>
                    </div>
                    <a href="{{ $alatInfo['url'] }}" target="_blank"
                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium flex items-center gap-1">
                        Buka Halaman
                        <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                    </a>
                </div>
                <div class="p-6">
                    <div class="flex gap-6">
                        @if($alatInfo['gambar'])
                            <div class="flex-shrink-0">
                                <img src="{{ $alatInfo['gambar'] }}" alt="{{ $alatInfo['nama_alat'] }}"
                                    class="w-28 h-28 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                            </div>
                        @endif
                        <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kode</div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                                    {{ $alatInfo['kode_alat'] }}</div>
                            </div>
                            <div class="col-span-2">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nama Alat</div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                                    {{ $alatInfo['nama_alat'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kategori</div>
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                    {{ $alatInfo['kategori'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Stok</div>
                                <div
                                    class="text-sm font-bold mt-1 {{ $alatInfo['stok'] > 2 ? 'text-success-600' : ($alatInfo['stok'] > 0 ? 'text-warning-600' : 'text-danger-600') }}">
                                    {{ $alatInfo['stok'] }} unit
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kondisi</div>
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                    {{ $alatInfo['kondisi'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Harga Satuan</div>
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                    {{ $alatInfo['harga'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Sedang Dipinjam</div>
                                <div class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">
                                    {{ $alatInfo['sedang_dipinjam'] }} peminjaman</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($peminjamanInfo)
            <div
                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 animate-in slide-in-from-bottom-4 duration-500">
                <div
                    class="fi-section-header flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2 flex-1">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10">
                            <x-heroicon-o-document-text class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        </span>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Info Peminjaman Ditemukan</h3>
                    </div>
                    <a href="{{ $peminjamanInfo['url'] }}" target="_blank"
                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium flex items-center gap-1">
                        Buka Halaman
                        <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                    </a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nomor
                            </div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $peminjamanInfo['nomor'] }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Peminjam</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                                {{ $peminjamanInfo['peminjam'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal Pinjam</div>
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ $peminjamanInfo['tanggal_pinjam'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Rencana Kembali</div>
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                                {{ $peminjamanInfo['tanggal_kembali'] }}</div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                            Status</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            {{ $peminjamanInfo['status_color'] === 'success' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : '' }}
                            {{ $peminjamanInfo['status_color'] === 'warning' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400' : '' }}
                            {{ $peminjamanInfo['status_color'] === 'danger' ? 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400' : '' }}
                            {{ $peminjamanInfo['status_color'] === 'info' ? 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-400' : '' }}
                            {{ $peminjamanInfo['status_color'] === 'gray' ? 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400' : '' }}
                        ">
                            {{ $peminjamanInfo['status'] }}
                        </span>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                            Barang Dipinjam</div>
                        <div class="space-y-2">
                            @foreach($peminjamanInfo['items'] as $item)
                                <div
                                    class="flex items-center justify-between px-4 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $item['nama'] }}</span>
                                    <span
                                        class="text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 px-2.5 py-1 rounded-full">
                                        {{ $item['jumlah'] }} unit
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errorMessage)
            <div
                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-danger-200 dark:bg-gray-900 dark:ring-danger-500/20">
                <div class="p-6 flex items-center gap-4">
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-danger-50 dark:bg-danger-500/10">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-danger-600 dark:text-danger-400" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-danger-600 dark:text-danger-400">QR Tidak Dikenali</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $errorMessage }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            function qrScanner() {
                return {
                    scanning: false,
                    scanner: null,

                    init() {
                        // Cleanup on navigation
                        document.addEventListener('livewire:navigating', () => {
                            this.stopScanner();
                        });
                    },

                    toggleScanner() {
                        if (this.scanning) {
                            this.stopScanner();
                        } else {
                            this.startScanner();
                        }
                    },

                    startScanner() {
                        this.scanning = true;

                        this.$nextTick(() => {
                            this.scanner = new Html5Qrcode("qr-reader");
                            this.scanner.start(
                                { facingMode: "environment" },
                                {
                                    fps: 10,
                                    qrbox: { width: 250, height: 250 },
                                    aspectRatio: 1.0,
                                },
                                (decodedText) => {
                                    // Success - send to Livewire
                                    this.stopScanner();
                                    @this.call('processScanResult', decodedText);
                                },
                                (errorMessage) => {
                                    // Ignore scan errors (no QR found in frame)
                                }
                            ).catch(err => {
                                console.error('Camera error:', err);
                                this.scanning = false;
                            });
                        });
                    },

                    stopScanner() {
                        if (this.scanner) {
                            this.scanner.stop().then(() => {
                                this.scanner.clear();
                                this.scanning = false;
                            }).catch(() => {
                                this.scanning = false;
                            });
                        } else {
                            this.scanning = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-filament-panels::page>