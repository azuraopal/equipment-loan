<div class="space-y-3">
    @foreach($this->record->peminjaman->alats as $alat)
        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
            @if($alat->gambar)
                <img src="{{ Storage::url($alat->gambar) }}" class="w-12 h-12 rounded-lg object-cover">
            @else
                <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 dark:bg-gray-700">
                    <x-heroicon-o-photo class="w-6 h-6" />
                </div>
            @endif

            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $alat->nama }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah: {{ $alat->pivot->jumlah }} unit</p>
            </div>
        </div>
    @endforeach
</div>