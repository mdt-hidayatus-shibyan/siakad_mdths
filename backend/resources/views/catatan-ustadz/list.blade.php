@forelse($catatans as $c)
    @php
        // Warna Urgensi
        $urgensiClass = match ($c->tingkat_urgensi) {
            'Penting / Mendesak' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
            'Tinggi' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
            'Sedang' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
            default => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        };

        // Badge Kategori Icon
        $kategoriIcon = match ($c->kategori) {
            'Keluhan Murid' => 'bi-person-exclamation',
            'KBM & Perkembangan Akademik' => 'bi-book-half',
            'Fasilitas Madrasah' => 'bi-building-exclamation',
            'Evaluasi & Saran' => 'bi-lightbulb-fill',
            default => 'bi-chat-left-text-fill',
        };
    @endphp

    <div
        class="m3-glass-card p-4 md:p-5 group transition-all hover:border-primary/40 dark:hover:border-primary-dark/40 shadow-2xs relative overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">

            <!-- Left Info -->
            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                <!-- Avatar Ustadz -->
                <div
                    class="w-11 h-11 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-lg text-zinc-500 dark:text-zinc-400 shrink-0 shadow-2xs relative">
                    <i class="bi bi-person-fill"></i>
                </div>

                <div class="flex-1 min-w-0">

                    <!-- Top Meta Tags -->
                    <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                        <!-- Ustadz Name -->
                        <span class="inline-flex items-center gap-1 text-xs font-black text-zinc-900 dark:text-white">
                            {{ $c->ustadz->nama_lengkap ?? 'Ustadz' }}
                        </span>
                        <span class="text-zinc-300 dark:text-zinc-700">•</span>

                        <!-- Kategori Badge -->
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                            <i class="bi {{ $kategoriIcon }} text-[9px]"></i>
                            {{ $c->kategori }}
                        </span>

                        <!-- Urgensi Badge -->
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border shadow-2xs {{ $urgensiClass }}">
                            <i class="bi bi-shield-exclamation text-[9px]"></i>
                            {{ $c->tingkat_urgensi }}
                        </span>

                        <!-- Target Badge -->
                        @if ($c->target_tipe === 'murid' && $c->murid)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 shadow-2xs">
                                <i class="bi bi-person-badge text-[9px]"></i> Murid: {{ $c->murid->nama_lengkap }}
                                ({{ $c->murid->nism }})
                            </span>
                        @elseif($c->target_tipe === 'madrasah')
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20 shadow-2xs">
                                <i class="bi bi-building text-[9px]"></i> Madrasah
                            </span>
                        @endif
                    </div>

                    <!-- Judul Catatan -->
                    <h4 class="text-sm md:text-base font-black text-zinc-900 dark:text-white tracking-tight mb-1">
                        {{ $c->judul }}
                    </h4>

                    <!-- Potongan Isi Catatan -->
                    <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300 line-clamp-2 mb-2 leading-relaxed">
                        {{ $c->isi_catatan }}
                    </p>

                    <!-- Bottom Timestamp & Indicator -->
                    <div
                        class="flex flex-wrap items-center gap-3 text-[11px] font-bold text-zinc-400 dark:text-zinc-500 font-mono">
                        <span class="flex items-center gap-1">
                            <i class="bi bi-clock text-[10px]"></i> {{ $c->created_at->translatedFormat('d F Y, H:i') }}
                            WIB
                        </span>

                        @if ($c->lampiran_foto)
                            <span
                                class="flex items-center gap-1 text-primary dark:text-primary-dark font-sans font-black">
                                <i class="bi bi-image text-xs"></i> Ada Lampiran Foto
                            </span>
                        @endif

                        @if ($c->dibaca_admin_pada)
                            <span
                                class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-sans font-bold">
                                <i class="bi bi-check2-all text-sm"></i> Sudah dibaca admin
                            </span>
                        @else
                            <span
                                class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-sans font-bold">
                                <i class="bi bi-envelope-open text-xs"></i> Baru / Belum dibaca
                            </span>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Right Actions (Read-Only Detail) -->
            <div
                class="flex items-center sm:flex-col justify-end gap-2 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-zinc-200/60 dark:border-zinc-800">
                <button type="button" onclick="showDetailModal('{{ route('catatan-ustadz.show', $c->id) }}')"
                    class="m3-btn-primary h-9 px-4 text-xs font-black shadow-2xs flex items-center gap-1.5 group/btn">
                    <i class="bi bi-eye-fill text-xs"></i>
                    <span>Buka Catatan</span>
                </button>
            </div>

        </div>

    </div>
@empty
    <x-empty-state icon="bi-journal-x" title="Belum Ada Catatan Ustadz"
        message="Tidak ada catatan atau keluhan yang sesuai dengan kriteria filter." />
@endforelse

@if ($catatans->hasPages())
    <div class="mt-4 m3-glass-card p-4 sm:p-5 relative z-10">
        {{ $catatans->links('vendor.pagination.custom') }}
    </div>
@endif
