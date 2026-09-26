@section('title', 'Riwayat Kas - ' . ($murid->nama_lengkap ?? $murid->nama))

<x-app-layout>

    <!-- 1. HEADER -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div class="flex items-center gap-3.5">
            <!-- Tombol Kembali -->
            <a href="{{ route('kas-ruangan.show', $ruangan->id) }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-2xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0"
                title="Kembali ke Ruangan">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5 shadow-2xs">
                        <i class="bi bi-door-open-fill text-[10px]"></i>
                        <span>{{ $ruangan->nama_ruangan }}</span>
                    </span>
                    <span class="text-xs font-bold text-zinc-400 font-mono">
                        NISM: {{ $murid->nism ?? '-' }}
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ $murid->nama_lengkap ?? $murid->nama }}
                </h2>
                <p class="text-xs md:text-[13px] text-zinc-500 dark:text-zinc-400 font-medium mt-0.5">
                    Rincian riwayat cicilan iuran kas murid pada ruangan kelas aktif.
                </p>
            </div>
        </div>
    </div>

    @php
        $totalTerkumpul = $riwayats->sum('jumlah_bayar');
        $jumlahTransaksi = $riwayats->count();
        $isLaki = strtolower($murid->jenis_kelamin ?? 'l') === 'l';
        $targetIuran = $isLaki
            ? $ruangan->pengaturanKas->nominal_laki ?? 0
            : $ruangan->pengaturanKas->nominal_perempuan ?? 0;
        $sisaTarget = max(0, $targetIuran - $totalTerkumpul);
    @endphp

    <!-- 2. RINGKASAN PEMBAYARAN murid (M3 Glass Card) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 md:gap-4 mb-6 relative z-10">
        <!-- Card 1: Total Telah Dibayar -->
        <div class="m3-glass-card p-5 rounded-3xl flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">
                    Total Dibayar
                </p>
                <h3
                    class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight leading-none font-mono">
                    Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <!-- Card 2: Target Iuran & Sisa -->
        <div class="m3-glass-card p-5 rounded-3xl flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="bi bi-bullseye"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">
                    Sisa Target (Kurang)
                </p>
                <h3
                    class="text-xl md:text-2xl font-black {{ $sisaTarget > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} tracking-tight leading-none font-mono">
                    Rp {{ number_format($sisaTarget, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <!-- Card 3: Frekuensi Transaksi -->
        <div class="m3-glass-card p-5 rounded-3xl flex items-center gap-3.5 shadow-2xs">
            <div
                class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">
                    Total Transaksi
                </p>
                <h3
                    class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-none font-mono">
                    {{ $jumlahTransaksi }} <span class="text-xs font-bold text-zinc-400 font-sans">Kali Cicilan</span>
                </h3>
            </div>
        </div>
    </div>

    <!-- 3. DAFTAR RIWAYAT TRANSAKSI -->
    <div class="flex flex-col gap-3 relative z-10">
        @forelse ($riwayats as $item)
            <div
                class="m3-glass-card p-4.5 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all shadow-2xs hover:border-emerald-500/40 group bg-white/70 dark:bg-zinc-900/60 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800">

                <!-- Tanggal & Nominal Bayar -->
                <div class="flex items-center gap-3.5 md:w-[40%] shrink-0">
                    <div
                        class="w-11 h-11 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 flex items-center justify-center text-lg shadow-2xs shrink-0">
                        <i class="bi bi-calendar2-check"></i>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">
                            {{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y') }}
                        </p>
                        <h4
                            class="font-black text-zinc-900 dark:text-white text-base tracking-tight leading-tight font-mono">
                            Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>

                <!-- Status Penyetoran ke Madrasah -->
                <div
                    class="flex-1 flex items-center md:justify-center border-t border-b md:border-none border-zinc-200/80 dark:border-zinc-800 py-2.5 md:py-0">
                    @if ($item->is_disetor)
                        <span
                            class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-black uppercase tracking-wider shadow-2xs inline-flex items-center gap-1.5">
                            <i class="bi bi-lock-fill text-xs text-emerald-500"></i> Disetor ke Tabungan
                        </span>
                    @else
                        <span
                            class="px-3 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-full text-[10px] font-black uppercase tracking-wider shadow-2xs inline-flex items-center gap-1.5">
                            <i class="bi bi-wallet2 text-xs text-amber-500"></i> Fisik di Wali Kelas
                        </span>
                    @endif
                </div>

                <!-- Tombol Aksi Edit & Hapus -->
                <div class="flex items-center gap-2 md:w-[130px] shrink-0 justify-end">
                    @if ($item->is_disetor)
                        <span
                            class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 italic uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-2xs w-full text-center flex items-center justify-center gap-1">
                            <i class="bi bi-shield-lock-fill text-[11px]"></i> Terkunci
                        </span>
                    @else
                        <button type="button"
                            onclick="bukaModalEdit({{ $item->id }}, '{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('Y-m-d') }}', {{ $item->jumlah_bayar }})"
                            class="w-9 h-9 flex items-center justify-center bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-xl transition-all shadow-2xs active:scale-90 outline-none"
                            title="Edit Pembayaran">
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </button>
                        <button type="button" onclick="hapusRiwayat({{ $item->id }})"
                            class="w-9 h-9 flex items-center justify-center bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-xl transition-all shadow-2xs active:scale-90 outline-none"
                            title="Hapus Catatan">
                            <i class="bi bi-trash3-fill text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state icon="bi-receipt" title="Belum Ada Transaksi"
                message="Catatan pembayaran kas dari murid ini akan muncul di sini." />
        @endforelse
    </div>

    <!-- ========================================== -->
    <!-- MODAL EDIT CICILAN                         -->
    <!-- ========================================== -->
    <div id="modalEdit"
        class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center hidden backdrop-blur-sm p-4 transition-all">
        <div class="m3-glass-card !bg-white dark:!bg-[#0c0c0e] w-full max-w-sm p-6 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 mx-auto relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
            id="modalEditContent">

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs">
                        <i class="bi bi-pencil-fill text-base"></i>
                    </div>
                    <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight">Edit Cicilan</h3>
                </div>

                <form id="formEdit" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Tanggal Bayar -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Tanggal Bayar
                        </label>
                        <input type="date" name="tanggal_bayar" id="editTanggal" required
                            class="m3-input-glass w-full text-xs font-bold rounded-2xl">
                    </div>

                    <!-- Jumlah Bayar -->
                    <div>
                        <label
                            class="block text-[11px] font-black text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5 ml-1">
                            Jumlah Bayar (Rp)
                        </label>
                        <div class="relative group">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-black text-zinc-400 pointer-events-none text-xs font-mono">Rp</span>
                            <input type="number" name="jumlah_bayar" id="editJumlah" required min="1"
                                class="m3-input-glass w-full !pl-10 font-mono font-black text-base text-zinc-900 dark:text-white rounded-2xl">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2.5 pt-3 border-t border-zinc-200/80 dark:border-zinc-800">
                        <button type="button" onclick="tutupModalEdit()"
                            class="flex-1 h-11 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-2xl shadow-2xs transition-all outline-none active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 h-11 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-2xl shadow-md transition-all active:scale-95 outline-none">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Hidden untuk Hapus Riwayat -->
    <form id="formHapus" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const baseRouteUrl = "{{ url('kas-ruangan/bayar') }}";

        function bukaModalEdit(id, tgl, jml) {
            const form = document.getElementById('formEdit');
            form.action = `${baseRouteUrl}/${id}`;

            document.getElementById('editTanggal').value = tgl;
            document.getElementById('editJumlah').value = jml;

            const modal = document.getElementById('modalEdit');
            const content = document.getElementById('modalEditContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalEdit() {
            const modal = document.getElementById('modalEdit');
            const content = document.getElementById('modalEditContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function hapusRiwayat(id) {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: '<span class="text-base font-black tracking-tight">Hapus Catatan?</span>',
                html: '<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Total kas akan otomatis berkurang. Tindakan ini tidak dapat dibatalkan!</p>',
                icon: 'warning',
                showCancelButton: true,
                heightAuto: false,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#71717a',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: isDark ? '#0c0c0e' : '#ffffff',
                color: isDark ? '#f4f4f5' : '#18181b',
                customClass: {
                    popup: '!rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6',
                    confirmButton: 'h-10 px-5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none',
                    cancelButton: 'h-10 px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '<span class="text-base font-black tracking-tight">Menghapus...</span>',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        background: isDark ? '#0c0c0e' : '#ffffff',
                        color: isDark ? '#f4f4f5' : '#18181b',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const form = document.getElementById('formHapus');
                    form.action = `${baseRouteUrl}/${id}`;
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
