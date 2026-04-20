<x-filament-panels::page>
    <style>
        /* ── Suppress semua UI bawaan html5-qrcode ── */
        #qr-reader > img,
        #qr-reader__header_message,
        #qr-reader__status_span,
        #qr-reader__dashboard_section_csr,
        #qr-reader__dashboard_section_fsr,
        #qr-reader__dashboard,
        #qr-reader__filescan_input,
        #qr-reader select,
        #qr-reader button,
        #qr-reader__scan_region img {
            display: none !important;
        }
        #qr-reader {
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }
        #qr-reader__scan_region {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
        }
        #qr-reader video {
            border-radius: 12px !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
        }
        @keyframes scanLine {
            0%   { top: 20%; opacity: 0.7; }
            50%  { opacity: 1; }
            100% { top: 80%; opacity: 0.7; }
        }
        .qr-scan-line {
            animation: scanLine 2s ease-in-out infinite;
        }
    </style>

    <div x-data="qrScanner()" x-init="init()" class="space-y-4 max-w-2xl mx-auto">

        {{-- ── Scanner Card ── --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex-shrink-0">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Scanner QR Code</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Arahkan ke QR alat atau surat peminjaman</p>
                    </div>
                </div>

                <button
                    @click="toggleScanner()"
                    :class="scanning ? 'bg-red-500 hover:bg-red-600' : 'bg-primary-600 hover:bg-primary-700'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-white text-xs font-semibold transition-colors duration-150 flex-shrink-0">
                    <template x-if="!scanning">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 9V5a2 2 0 012-2h4M15 3h4a2 2 0 012 2v4M21 15v4a2 2 0 01-2 2h-4M9 21H5a2 2 0 01-2-2v-4" />
                            </svg>
                            Mulai Scan
                        </span>
                    </template>
                    <template x-if="scanning">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Stop
                        </span>
                    </template>
                </button>
            </div>

            {{-- Camera Body --}}
            <div class="p-5">
                {{-- Active Scanner --}}
                <div
                    x-show="scanning"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative rounded-xl overflow-hidden bg-gray-950"
                    style="max-width: 420px; margin: 0 auto; min-height: 280px;">

                    <div id="qr-reader" class="w-full"></div>

                    {{-- Overlay: corners + scan line --}}
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="relative w-44 h-44">
                            <span class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-white/90 rounded-tl-md"></span>
                            <span class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-white/90 rounded-tr-md"></span>
                            <span class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-white/90 rounded-bl-md"></span>
                            <span class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-white/90 rounded-br-md"></span>
                            <span class="absolute left-2 right-2 h-px bg-primary-400/70 qr-scan-line" style="position: absolute;"></span>
                        </div>
                    </div>
                </div>

                {{-- Idle --}}
                <div
                    x-show="!scanning"
                    class="flex flex-col items-center justify-center py-10 gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400 dark:text-gray-500 text-center">
                        Klik <span class="font-semibold text-primary-600 dark:text-primary-400">Mulai Scan</span> untuk mengaktifkan kamera
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Alat Info ── --}}
        @if($alatInfo)
            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Info Alat</span>
                    </div>
                    <a href="{{ $alatInfo['url'] }}" target="_blank"
                        class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors">
                        Buka halaman
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
                <div class="p-5">
                    <div class="flex gap-4">
                        @if($alatInfo['gambar'])
                            <img src="{{ $alatInfo['gambar'] }}" alt="{{ $alatInfo['nama_alat'] }}"
                                class="w-20 h-20 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700 flex-shrink-0">
                        @endif
                        <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-3">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Kode</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $alatInfo['kode_alat'] }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Nama Alat</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $alatInfo['nama_alat'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Kategori</p>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $alatInfo['kategori'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Stok</p>
                                <p class="text-sm font-bold mt-0.5 {{ $alatInfo['stok'] > 2 ? 'text-emerald-600 dark:text-emerald-400' : ($alatInfo['stok'] > 0 ? 'text-amber-500' : 'text-red-500') }}">
                                    {{ $alatInfo['stok'] }} unit
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Kondisi</p>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $alatInfo['kondisi'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Harga Satuan</p>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $alatInfo['harga'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Sedang Dipinjam</p>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $alatInfo['sedang_dipinjam'] }} peminjaman</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Peminjaman Info ── --}}
        @if($peminjamanInfo)
            <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Info Peminjaman</span>
                    </div>
                    <a href="{{ $peminjamanInfo['url'] }}" target="_blank"
                        class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors">
                        Buka halaman
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
                <div class="p-5 space-y-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Nomor</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $peminjamanInfo['nomor'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Peminjam</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $peminjamanInfo['peminjam'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Tanggal Pinjam</p>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $peminjamanInfo['tanggal_pinjam'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Rencana Kembali</p>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mt-0.5">{{ $peminjamanInfo['tanggal_kembali'] }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Status</p>
                        @php
                            $badgeClass = match($peminjamanInfo['status_color']) {
                                'success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                'danger'  => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                'info'    => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                default   => 'bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ $peminjamanInfo['status'] }}
                        </span>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Barang Dipinjam</p>
                        <div class="space-y-1.5">
                            @foreach($peminjamanInfo['items'] as $item)
                                <div class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $item['nama'] }}</span>
                                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 px-2.5 py-1 rounded-full">
                                        {{ $item['jumlah'] }} unit
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Error ── --}}
        @if($errorMessage)
            <div class="rounded-2xl bg-white dark:bg-gray-900 ring-1 ring-red-200 dark:ring-red-500/20 overflow-hidden">
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">QR Tidak Dikenali</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $errorMessage }}</p>
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
                        document.addEventListener('livewire:navigating', () => {
                            this.stopScanner();
                        });
                    },

                    toggleScanner() {
                        this.scanning ? this.stopScanner() : this.startScanner();
                    },

                    startScanner() {
                        this.scanning = true;
                        this.$nextTick(() => {
                            this.scanner = new Html5Qrcode("qr-reader");
                            this.scanner.start(
                                { facingMode: "environment" },
                                {
                                    fps: 10,
                                    qrbox: { width: 200, height: 200 },
                                    aspectRatio: 1.333,
                                    showTorchButtonIfSupported: false,
                                    showZoomSliderIfSupported: false,
                                    defaultZoomValueIfSupported: 1,
                                },
                                (decodedText) => {
                                    this.stopScanner();
                                    @this.call('processScanResult', decodedText);
                                },
                                () => {}
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
                                this.scanner = null;
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