<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($snapToken)
            <x-filament::button size="lg" class="w-full justify-center" id="pay-button" color="danger"
                icon="heroicon-o-credit-card">
                Bayar via Midtrans
            </x-filament::button>
        @endif

        <x-filament::button size="lg" class="w-full justify-center" color="warning" icon="heroicon-o-banknotes"
            wire:click="mountAction('bayarCash')" wire:loading.attr="disabled">
            Bayar Cash (Tunai)
        </x-filament::button>
    </div>

    <div class="mt-2 rounded-lg bg-gray-50 dark:bg-white/5 px-4 py-3">
        <p class="text-sm text-center text-gray-500 dark:text-gray-400">
            Pilih metode pembayaran yang sesuai. <strong>Midtrans</strong> untuk pembayaran online,
            <strong>Cash</strong> untuk pembayaran langsung ke petugas.
        </p>
    </div>

    @if($snapToken)
        @once
            <script
                src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                data-client-key="{{ config('services.midtrans.client_key') }}"></script>
            <script type="text/javascript">
                document.getElementById('pay-button').onclick = function () {
                    this.disabled = true;
                    this.classList.add('opacity-75', 'cursor-wait');

                    snap.pay('{{ $snapToken }}', {
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
    @endif
</div>