<form action="{{ route('peserta-imni.tarik') }}" method="POST"
    class="ajax-form relative z-10 flex flex-col max-h-[90vh]" data-refresh-target="#data-table-container">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahun->id }}">

    <!-- Modal Header -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                <i class="bi bi-cloud-arrow-down-fill"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                    Sinkronisasi Murid
                </span>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Tarik Murid Kelas Akhir
                </h3>
            </div>
        </div>
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="w-8.5 h-8.5 flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-5 md:p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
        <!-- Rangkuman Data Kandidat -->
        <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 text-xs space-y-2">
            <div class="flex justify-between items-center font-bold text-zinc-600 dark:text-zinc-400">
                <span>Tahun Pelajaran:</span>
                <span class="text-zinc-900 dark:text-white font-black">{{ $selectedTahun->nama_hijriyah }} H ({{ $selectedTahun->nama_masehi }} M)</span>
            </div>
            <div class="flex justify-between items-center font-bold text-zinc-600 dark:text-zinc-400">
                <span>Total Potensi Murid Akhir:</span>
                <span class="text-zinc-700 dark:text-zinc-300 font-black">{{ $kandidatTotal }} Murid</span>
            </div>
            <div class="border-t border-zinc-200/60 dark:border-zinc-700/60 pt-2 flex justify-between items-center">
                <span class="font-extrabold text-zinc-700 dark:text-zinc-300">Murid Belum Terdaftar IMNI:</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-black text-sm">{{ $belumDitarik }} Murid</span>
            </div>
        </div>

        <!-- 1. Pilihan Tingkat Target -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-extrabold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider ml-1">
                Tingkat Sasaran Penarikan
            </label>
            <div class="relative group/select">
                <select name="tingkat_id" id="tingkat_id" class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                    <option value="">Semua Tingkat Akhir (3 TPQ, 6 IBT, 3 TSA)</option>
                    @foreach ($daftarTingkat as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->nama_tingkat }} ({{ $t->kode_tingkat }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-zinc-400 mt-1">
                Pilih tingkat tertentu jika hanya ingin menarik murid dari jenjang tersebut.
            </p>
        </div>

        <!-- Notifikasi Fitur Otomatisasi -->
        <div class="p-3.5 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-700 dark:text-blue-300 flex items-start gap-2.5">
            <i class="bi bi-info-circle-fill text-blue-500 text-sm mt-0.5 shrink-0"></i>
            <div class="space-y-1 text-[11px]">
                <p class="font-bold">Proses otomatisasi yang akan dijalankan:</p>
                <ul class="list-disc list-inside space-y-0.5 text-zinc-600 dark:text-zinc-400">
                    <li>Generate Nomor Peserta IMNI: <strong>IMNI-{{ $selectedTahun->nama_hijriyah ? substr(preg_replace('/\D/', '', $selectedTahun->nama_hijriyah), -4) : '4748' }}-TINGKAT-NISM</strong></li>
                    <li>Pembuatan data Tagihan Pembayaran IMNI sesuai Pengaturan Tagihan</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-end gap-2.5 transition-colors duration-300">
        <button type="button" data-dismiss="modal" command="close" commandfor="dialog"
            class="m3-btn-secondary text-xs">
            Batal
        </button>
        <button type="submit" class="m3-btn-primary text-xs flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white">
            <i class="bi bi-cloud-arrow-down-fill"></i>
            <span>Mulai Tarik Data</span>
        </button>
    </div>
</form>
