<div style="display:flex; flex-direction:column; gap:16px;">

    <p
        style="font-size:11px; font-weight:500; letter-spacing:0.08em; text-transform:uppercase; color:#6b7280; margin:0;">
        Metode pembayaran
    </p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">

        <div style="border-radius:12px; border:1px solid {{ $snapToken ? '#3b82f620' : '#374151' }}; padding:16px; {{ $snapToken ? 'cursor:pointer;' : 'opacity:0.5; cursor:not-allowed;' }} transition:border-color 0.15s;"
            @if($snapToken) id="pay-button" role="button" onmouseenter="this.style.borderColor='#3b82f6'"
            onmouseleave="this.style.borderColor='#3b82f620'" @endif>
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <div
                    style="flex-shrink:0; width:36px; height:36px; border-radius:8px; background:{{ $snapToken ? '#1e3a5f' : '#1f2937' }}; display:flex; align-items:center; justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="{{ $snapToken ? '#60a5fa' : '#6b7280' }}" width="18" height="18" style="display:block;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <div style="flex:1; min-width:0;">
                    <p style="font-size:13px; font-weight:500; color:#f9fafb; margin:0 0 2px;">Pembayaran Online</p>
                    <p style="font-size:11px; color:#6b7280; margin:0 0 8px;">via Midtrans</p>
                    <p style="font-size:12px; color:#9ca3af; margin:0 0 10px; line-height:1.5;">Transfer bank, e-wallet,
                        QRIS, atau kartu kredit.</p>
                    @if(!$snapToken)
                        <span
                            style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 8px; border-radius:100px; background:#450a0a; color:#fca5a5;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" width="10" height="10" style="display:block; flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.051 3.378c.866-1.5 3.032-1.5 3.898 0l7.354 12.748ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            Tidak tersedia
                        </span>
                    @else
                        <span
                            style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 8px; border-radius:100px; background:#1e3a5f; color:#93c5fd;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" width="10" height="10" style="display:block; flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Pilih metode ini
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div style="border-radius:12px; border:1px solid #78350f40; padding:16px; cursor:pointer; transition:border-color 0.15s;"
            wire:click="mountAction('bayarCash')" wire:loading.attr="style"
            wire:loading.val="border-radius:12px; border:1px solid #78350f40; padding:16px; opacity:0.6; pointer-events:none;"
            role="button" onmouseenter="this.style.borderColor='#f59e0b'"
            onmouseleave="this.style.borderColor='#78350f40'">
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <div
                    style="flex-shrink:0; width:36px; height:36px; border-radius:8px; background:#422006; display:flex; align-items:center; justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="#fbbf24" width="18" height="18" style="display:block;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div style="flex:1; min-width:0;">
                    <p style="font-size:13px; font-weight:500; color:#f9fafb; margin:0 0 2px;">Pembayaran Tunai</p>
                    <p style="font-size:11px; color:#6b7280; margin:0 0 8px;">Cash ke petugas</p>
                    <p style="font-size:12px; color:#9ca3af; margin:0 0 10px; line-height:1.5;">Bayar langsung kepada
                        petugas, diverifikasi manual.</p>
                    <span
                        style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 8px; border-radius:100px; background:#422006; color:#fcd34d;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" width="10" height="10" style="display:block; flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                        Pilih metode ini
                    </span>
                </div>
            </div>
        </div>

    </div>

    <div
        style="display:flex; align-items:flex-start; gap:10px; padding:10px 14px; border-radius:8px; background:#111827; border:1px solid #1f2937;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b7280"
            width="14" height="14" style="display:block; flex-shrink:0; margin-top:1px;">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p style="font-size:12px; color:#6b7280; margin:0; line-height:1.6;">
            Pilih satu metode pembayaran untuk menyelesaikan denda. Status akan diperbarui setelah pembayaran
            dikonfirmasi.
        </p>
    </div>

    @if($snapToken)
        @once
            <script
                src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                data-client-key="{{ config('services.midtrans.client_key') }}"></script>
            <script>
                document.getElementById('pay-button').onclick = function () {
                    this.style.opacity = '0.65';
                    this.style.pointerEvents = 'none';
                    snap.pay('{{ $snapToken }}', {
                        onSuccess: function () { window.location.href = '{{ route("filament.peminjam.resources.pengembalian.index") }}'; },
                        onPending: function () { window.location.href = '{{ route("filament.peminjam.resources.pengembalian.index") }}'; },
                        onError: function () {
                            new FilamentNotification()
                                .title('Pembayaran gagal')
                                .body('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.')
                                .danger().send();
                            const btn = document.getElementById('pay-button');
                            if (btn) { btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
                        },
                        onClose: function () {
                            const btn = document.getElementById('pay-button');
                            if (btn) { btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
                        }
                    });
                };
            </script>
        @endonce
    @endif
</div>