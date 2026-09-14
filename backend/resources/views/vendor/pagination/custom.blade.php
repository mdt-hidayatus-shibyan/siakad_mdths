@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation"
        class="flex flex-col sm:flex-row items-center justify-between gap-4 relative z-10">

        <!-- Info Data (Compact Typography) -->
        <div
            class="text-[11px] sm:text-xs font-bold text-zinc-500 dark:text-zinc-500 text-center sm:text-left transition-colors duration-300">
            Menampilkan <span class="font-black text-primary dark:text-primary-dark">{{ $paginator->firstItem() }}</span>
            -
            <span class="font-black text-primary dark:text-primary-dark">{{ $paginator->lastItem() }}</span> dari
            <span class="font-black text-zinc-900 dark:text-white">{{ $paginator->total() }}</span> data
        </div>

        <!-- Tombol Navigasi (Compact M3 40px) -->
        <div class="flex flex-wrap items-center justify-center gap-1.5">

            <!-- Tombol Previous -->
            @if ($paginator->onFirstPage())
                <span
                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-zinc-50 dark:bg-black text-zinc-300 dark:text-zinc-800 border border-zinc-200 dark:border-zinc-800 cursor-not-allowed transition-colors duration-300">
                    <i class="bi bi-chevron-left text-xs font-bold"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-primary dark:hover:text-primary-dark hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all duration-300 shadow-sm dark:shadow-none hover:-translate-x-0.5 active:scale-95 outline-none">
                    <i class="bi bi-chevron-left text-xs font-bold"></i>
                </a>
            @endif

            <!-- Nomor Halaman (Desktop & Tablet) -->
            <div class="hidden sm:flex items-center gap-1.5">
                @foreach ($elements as $element)
                    <!-- Separator (...) -->
                    @if (is_string($element))
                        <span
                            class="w-10 h-10 flex items-center justify-center text-zinc-400 dark:text-zinc-600 font-black tracking-widest transition-colors duration-300">...</span>
                    @endif

                    <!-- Array Nomor Halaman -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <!-- Halaman Aktif (Material 3 Solid) -->
                                <span
                                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-primary dark:bg-primary-dark border border-primary dark:border-primary-dark text-white dark:text-zinc-900 font-bold text-sm shadow-sm scale-105 z-10 cursor-default transition-all duration-300">
                                    {{ $page }}
                                </span>
                            @else
                                <!-- Halaman Tidak Aktif -->
                                <a href="{{ $url }}"
                                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-[13px] hover:text-primary dark:hover:text-primary-dark hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all duration-300 shadow-sm dark:shadow-none hover:-translate-y-0.5 active:scale-95 outline-none">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <!-- Tombol Next -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:text-primary dark:hover:text-primary-dark hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all duration-300 shadow-sm dark:shadow-none hover:translate-x-0.5 active:scale-95 outline-none">
                    <i class="bi bi-chevron-right text-xs font-bold"></i>
                </a>
            @else
                <span
                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-zinc-50 dark:bg-black text-zinc-300 dark:text-zinc-800 border border-zinc-200 dark:border-zinc-800 cursor-not-allowed transition-colors duration-300">
                    <i class="bi bi-chevron-right text-xs font-bold"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
