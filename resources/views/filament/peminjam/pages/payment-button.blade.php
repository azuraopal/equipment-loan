<div class="mt-4 space-y-4">
    <div
        class="rounded-2xl border border-gray-800/60 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-4 shadow-lg shadow-red-900/30 dark:border-gray-700">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-red-300/80">
                    Pembayaran Denda
                </p>
                <p class="mt-1 text-xs text-gray-300/90">
                    Transaksi aman dan terlindungi. Kamu akan diarahkan ke halaman pembayaran Midtrans.
                </p>
            </div>

            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/30 text-red-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    class="h-5 w-5 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3.75 7.5h16.5M3.75 10.5h16.5m-14 4.5h4.5m-4.5 3h9.5A2.75 2.75 0 0 0 22 15.25v-6.5A2.75 2.75 0 0 0 19.25 6H4.75A2.75 2.75 0 0 0 2 8.75v6.5A2.75 2.75 0 0 0 4.75 18Z" />
                </svg>
            </div>
        </div>
    </div>

    <x-filament::button size="xl"
        class="w-full justify-center gap-2 rounded-xl font-semibold tracking-wide shadow-lg shadow-red-900/30"
        id="pay-button" color="danger">
        Bayar
    </x-filament::button>

    <p class="text-[11px] text-center text-gray-500 dark:text-gray-400">
        Dengan menekan tombol <span class="font-semibold text-gray-800 dark:text-gray-100">Bayar</span>, kamu
        setuju untuk melanjutkan ke Midtrans dan menyelesaikan pembayaran denda.
    </p>

    <div class="pt-1 text-center">
        <a href="{{ route('filament.peminjam.resources.pengembalian.index') }}"
            class="text-xs font-medium text-gray-500 hover:text-gray-900 underline-offset-4 hover:underline transition-colors dark:text-gray-400 dark:hover:text-white">
            Kembali ke daftar pengembalian
        </a>
    </div>

    @once
        <script
            src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function () {
                this.disabled = true;
                this.classList.add('opacity-75', 'cursor-wait');

                snap.pay('{{ $this->snapToken }}', {
                    onSuccess: function (result) {
                        window.location.reload();
                    },
                    onPending: function (result) {
                        window.location.reload();
                    },
                    onError: function (result) {
                        new FilamentNotification()
                            .title('Pembayaran gagal!')
                            .danger()
                            .send();

                        const btn = document.getElementById('pay-button');
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('opacity-75', 'cursor-wait');
                        }
                    },
                    onClose: function () {
                        const btn = document.getElementById('pay-button');
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('opacity-75', 'cursor-wait');
                        }
                    }
                });
            };
        </script>
    @endonce
</div>