@php
    $urgensiClass = match ($catatan->tingkat_urgensi) {
        'Penting / Mendesak' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
        'Tinggi' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
        'Sedang' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        default => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
    };
@endphp

<div class="space-y-4">
    <!-- Header Box -->
    <div
        class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 space-y-2.5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20">
                <i class="bi bi-tag-fill"></i> {{ $catatan->kategori }}
            </span>
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $urgensiClass }}">
                <i class="bi bi-shield-exclamation"></i> Urgensi: {{ $catatan->tingkat_urgensi }}
            </span>
        </div>

        <h3 class="text-base sm:text-lg font-black text-zinc-900 dark:text-white leading-tight">
            {{ $catatan->judul }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-zinc-200/60 dark:border-zinc-800 text-xs">
            <div>
                <span class="text-zinc-400 block text-[10px] uppercase font-black">Pengirim (Ustadz):</span>
                <span class="font-black text-zinc-800 dark:text-zinc-200 flex items-center gap-1 mt-0.5">
                    <i class="bi bi-person-fill text-primary"></i> {{ $catatan->ustadz->nama_lengkap ?? '-' }}
                </span>
            </div>
            <div>
                <span class="text-zinc-400 block text-[10px] uppercase font-black">Waktu Pencatatan:</span>
                <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono flex items-center gap-1 mt-0.5">
                    <i class="bi bi-calendar3"></i> {{ $catatan->created_at->translatedFormat('d F Y, H:i') }} WIB
                </span>
            </div>
        </div>
    </div>

    <!-- Informasi Sasaran (Jika Murid / Ruangan) -->
    @if ($catatan->target_tipe === 'murid' && $catatan->murid)
        <div
            class="p-3.5 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base shrink-0">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Murid
                    yang Dilaporkan:</span>
                <h4 class="text-xs sm:text-sm font-black text-zinc-900 dark:text-white truncate">
                    {{ $catatan->murid->nama_lengkap }} <span class="font-mono text-zinc-500 font-bold">(NISM:
                        {{ $catatan->murid->nism }})</span>
                </h4>
            </div>
        </div>
    @elseif($catatan->target_tipe === 'madrasah')
        <div
            class="p-3.5 rounded-2xl bg-blue-500/5 dark:bg-blue-500/10 border border-blue-500/20 flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base shrink-0">
                <i class="bi bi-building"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 block">Sasaran:</span>
                <h4 class="text-xs sm:text-sm font-black text-zinc-900 dark:text-white">
                    Madrasah, Sarana & Prasarana, / Fasilitas
                </h4>
            </div>
        </div>
    @endif

    <!-- Isi Catatan Lengkap -->
    <div>
        <label class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">
            Isi Catatan / Keterangan Lengkap
        </label>
        <div
            class="p-4 rounded-2xl bg-zinc-50/70 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 text-xs sm:text-sm leading-relaxed text-zinc-800 dark:text-zinc-200 font-medium whitespace-pre-line select-text">
            {{ $catatan->isi_catatan }}
        </div>
    </div>

    <!-- Lampiran Foto (Jika Ada) -->
    @if ($catatan->lampiran_foto)
        <div>
            <label
                class="block text-[11px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">
                Lampiran Foto / Bukti
            </label>
            <div
                class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 max-h-80 bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center">
                <a href="{{ $catatan->lampiran_foto_url }}" target="_blank" title="Lihat ukuran penuh">
                    <img src="{{ $catatan->lampiran_foto_url }}" alt="Lampiran Catatan"
                        class="w-full h-auto max-h-80 object-contain hover:scale-105 transition-transform duration-200">
                </a>
            </div>
        </div>
    @endif
</div>
