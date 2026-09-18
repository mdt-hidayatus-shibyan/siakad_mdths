@section('title', 'Detail Catatan Ustadz')

<x-app-layout>

    <div class="mb-6 md:mb-8 flex items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('catatan-ustadz.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none border border-zinc-200 dark:border-zinc-700"
                title="Kembali ke Daftar">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Detail Catatan Ustadz
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Informasi catatan pendidik (Read-Only).
                </p>
            </div>
        </div>
    </div>

    <div class="m3-glass-card p-6 md:p-8 max-w-3xl relative z-10 shadow-2xs">
        @include('catatan-ustadz.detail-modal', ['catatan' => $catatan])
    </div>

</x-app-layout>
