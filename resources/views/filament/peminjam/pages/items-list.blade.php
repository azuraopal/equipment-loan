<div class="space-y-3">
    @foreach($record->peminjaman->alats as $alat)
        <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50/80 dark:bg-white/5 border border-gray-200/50 dark:border-white/10 hover:border-primary-500/30 dark:hover:border-primary-400/30 transition-colors">
            @if($alat->gambar)
                <img src="{{ Storage::url($alat->gambar) }}" alt="{{ $alat->nama }}" class="w-14 h-14 rounded-xl object-cover ring-2 ring-gray-200/50 dark:ring-white/10">
            @else
                <div class="w-14 h-14 rounded-xl bg-primary-500/10 dark:bg-primary-500/20 flex items-center justify-center">
                    <x-heroicon-o-cube class="w-7 h-7 text-primary-500 dark:text-primary-400" />
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $alat->nama }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    <span class="inline-flex items-center gap-1">
                        <x-heroicon-o-cube class="w-3.5 h-3.5" />
                        {{ $alat->pivot->jumlah }} unit
                    </span>
                </p>
            </div>
        </div>
    @endforeach
</div>