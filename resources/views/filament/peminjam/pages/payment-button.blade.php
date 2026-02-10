<div class="mt-4">
    <x-filament::button size="xl" class="w-full" id="pay-button" color="danger">
        Bayar Sekarang via Midtrans
    </x-filament::button>

    <div class="mt-4 text-center">
        <a href="{{ route('filament.peminjam.resources.pengembalian.index') }}"
            class="text-sm text-gray-500 hover:text-gray-900 underline transition-colors dark:text-gray-400 dark:hover:text-white">
            Kembali ke Daftar
        </a>
    </div>

    @once
        <script
            src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function () {
                snap.pay('{{ $this->snapToken }}', {
                    onSuccess: function (result) {
                        window.location.href = "{{ route('filament.peminjam.resources.pengembalian.index') }}";
                    },
                    onPending: function (result) {
                        window.location.href = "{{ route('filament.peminjam.resources.pengembalian.index') }}";
                    },
                    onError: function (result) {
                        new FilamentNotification()
                            .title('Pembayaran gagal!')
                            .danger()
                            .send();
                    },
                    onClose: function () {
                    }
                });
            };
        </script>
    @endonce
</div>