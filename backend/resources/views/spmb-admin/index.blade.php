@section('title', 'Penerimaan Murid Baru (SPMB)')

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-20">
        <div class="flex items-center gap-3.5">
            <div
                class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:bg-primary-dark/20 dark:text-primary-dark flex items-center justify-center border border-primary/20 shrink-0">
                <i class="bi bi-mortarboard-fill text-lg"></i>
            </div>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Penerimaan Murid Baru (SPMB)
                </h2>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 uppercase tracking-wider">
                    Verifikasi Pendaftaran Calon Murid Baru & Penerbitan NISM Otomatis
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Header -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="bukaModalScan()"
                class="m3-btn-primary h-10 px-4 text-xs font-black shadow-sm gap-2">
                <i class="bi bi-qr-code-scan text-sm"></i>
                <span>Scan / Cari Barcode</span>
            </button>

            <a href="{{ route('spmb.form') }}" target="_blank"
                class="m3-btn-secondary h-10 px-4 text-xs font-black gap-2">
                <i class="bi bi-box-arrow-up-right text-xs"></i>
                <span>Link SPMB Publik</span>
            </a>

            <a href="{{ route('spmb-admin.export-excel', ['tahun_pelajaran_id' => $tahunId, 'status' => $status]) }}"
                class="m3-btn-secondary h-10 px-3.5 text-xs font-black gap-1.5" title="Export ke Excel">
                <i class="bi bi-file-earmark-excel-fill text-emerald-600"></i>
                <span>Excel</span>
            </a>
        </div>
    </div>

    <!-- Statistik SPMB -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4 mb-6">
        <!-- Total Pendaftar -->
        <div class="m3-glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block">Total Pendaftar</span>
                <span class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mt-0.5 block font-mono">
                    {{ $stats['total'] }}
                </span>
            </div>
            <div
                class="w-11 h-11 rounded-2xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center text-lg border border-blue-500/20">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="m3-glass-card p-4 sm:p-5 flex items-center justify-between border-amber-500/30 bg-amber-500/5">
            <div>
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Menunggu
                    Verifikasi</span>
                <span class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-0.5 block font-mono">
                    {{ $stats['menunggu'] }}
                </span>
            </div>
            <div
                class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-500/30">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>

        <!-- Diterima -->
        <div class="m3-glass-card p-4 sm:p-5 flex items-center justify-between border-emerald-500/30 bg-emerald-500/5">
            <div>
                <span
                    class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Diterima
                    (Aktif)</span>
                <span
                    class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block font-mono">
                    {{ $stats['diterima'] }}
                </span>
            </div>
            <div
                class="w-11 h-11 rounded-2xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/30">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>

        <!-- Ditolak -->
        <div class="m3-glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-rose-500 block">Ditolak</span>
                <span class="text-2xl sm:text-3xl font-black text-rose-500 mt-0.5 block font-mono">
                    {{ $stats['ditolak'] }}
                </span>
            </div>
            <div
                class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 flex items-center justify-center text-lg border border-rose-500/20">
                <i class="bi bi-x-circle-fill"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="m3-glass-card p-4 mb-6">
        <form action="{{ route('spmb-admin.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Filter Tahun Pelajaran -->
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Tahun
                    Pelajaran</label>
                <select name="tahun_pelajaran_id" class="m3-input-glass w-full text-xs font-bold"
                    onchange="this.form.submit()">
                    @foreach ($daftarTahun as $tp)
                        <option value="{{ $tp->id }}" {{ $tahunId == $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama_hijriyah }} ({{ $tp->nama_masehi }}) {{ $tp->is_active ? '• [Aktif]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Status
                    Pendaftaran</label>
                <select name="status" class="m3-input-glass w-full text-xs font-bold" onchange="this.form.submit()">
                    <option value="Semua" {{ $status == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Menunggu Verifikasi" {{ $status == 'Menunggu Verifikasi' ? 'selected' : '' }}>
                        Menunggu Verifikasi</option>
                    <option value="Diterima" {{ $status == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                    <option value="Ditolak" {{ $status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Filter Level / Jenjang -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Jenjang /
                    Level</label>
                <select name="level_id" class="m3-input-glass w-full text-xs font-bold" onchange="this.form.submit()">
                    <option value="">Semua Level</option>
                    @foreach ($levels as $lvl)
                        <option value="{{ $lvl->id }}" {{ $levelId == $lvl->id ? 'selected' : '' }}>
                            {{ $lvl->nama_level }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Input -->
            <div class="sm:col-span-4 flex items-end gap-2">
                <div class="w-full">
                    <label
                        class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1 ml-0.5">Pencarian</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Nama / No Reg / KK / NIK..." class="m3-input-glass w-full text-xs font-bold">
                </div>
                <button type="submit" class="m3-btn-primary h-10 px-4 text-xs font-black shrink-0">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="m3-glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr
                        class="border-b border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 text-[10px] font-black text-zinc-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">No. Registrasi</th>
                        <th class="py-3.5 px-4">Calon Murid</th>
                        <th class="py-3.5 px-4">Kartu Keluarga & Wali</th>
                        <th class="py-3.5 px-4">Jenjang Masuk</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">NISM Resmi</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800/60">
                    @forelse ($pendaftar as $row)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group">
                            <!-- No Registrasi -->
                            <td class="py-3.5 px-4">
                                <span
                                    class="font-mono font-black text-primary dark:text-primary-dark block text-[11px]">
                                    {{ $row->nomor_pendaftaran }}
                                </span>
                                <span class="text-[10px] text-zinc-400">
                                    {{ $row->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <!-- Calon Murid -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0 border border-zinc-200 dark:border-zinc-700">
                                        @if ($row->foto)
                                            <img src="{{ asset('storage/' . $row->foto) }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-zinc-400 font-black text-xs">
                                                {{ $row->jenis_kelamin }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-black text-zinc-900 dark:text-white uppercase">
                                            {{ $row->nama_lengkap }}
                                        </h4>
                                        <p class="text-[10px] text-zinc-500">
                                            {{ $row->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }} • NIK:
                                            {{ mask_nik($row->nik) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Wali & KK -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $row->waliMurid->nama_kepala_keluarga ?? '-' }}
                                </div>
                                <p class="text-[10px] font-mono text-zinc-400">
                                    KK: {{ mask_kk($row->waliMurid->no_kk ?? null) }} •
                                    {{ $row->waliMurid->kampung->nama_kampung ?? '-' }}
                                </p>
                            </td>

                            <!-- Level -->
                            <td class="py-3.5 px-4">
                                <span class="font-black text-zinc-900 dark:text-white block">
                                    {{ $row->level->nama_level ?? '-' }}
                                </span>
                                <span class="text-[10px] text-zinc-400">
                                    {{ $row->level->tingkat->nama_tingkat ?? '' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $row->status_pendaftaran == 'Diterima' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' : ($row->status_pendaftaran == 'Ditolak' ? 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30' : 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30') }}">
                                    {{ $row->status_pendaftaran }}
                                </span>
                            </td>

                            <!-- NISM -->
                            <td class="py-3.5 px-4 font-mono font-bold">
                                @if ($row->nism_diberikan)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">
                                        {{ $row->nism_diberikan }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 text-[10px] italic">Belum terbit</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($row->status_pendaftaran !== 'Diterima')
                                        <!-- Tombol Verifikasi & Terbitkan NISM -->
                                        <button type="button" onclick="bukaModalVerifikasi({{ $row->id }})"
                                            class="m3-btn-primary h-8 px-3 text-[11px] font-black gap-1 shadow-xs"
                                            title="Verifikasi & Terbitkan NISM">
                                            <i class="bi bi-check2-circle text-xs"></i>
                                            <span>Verifikasi</span>
                                        </button>

                                        <!-- Tombol Tolak -->
                                        <button type="button"
                                            onclick="bukaModalTolak({{ $row->id }}, '{{ $row->nama_lengkap }}')"
                                            class="m3-btn-secondary h-8 w-8 !p-0 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 inline-flex items-center justify-center"
                                            title="Tolak Pendaftaran">
                                            <i class="bi bi-x-lg text-xs"></i>
                                        </button>
                                    @else
                                        <!-- Tombol Cetak Bukti Diterima -->
                                        <a href="{{ route('spmb-admin.cetak-diterima', $row->id) }}" target="_blank"
                                            class="m3-btn-secondary h-8 px-3 text-[11px] font-bold gap-1 inline-flex items-center"
                                            title="Cetak Surat Penerimaan">
                                            <i class="bi bi-printer text-xs"></i>
                                            <span>Surat Diterima</span>
                                        </a>
                                    @endif

                                    <!-- Kartu Pendaftaran -->
                                    <a href="{{ route('spmb.bukti', $row->nomor_pendaftaran) }}" target="_blank"
                                        class="m3-btn-secondary h-8 w-8 !p-0 inline-flex items-center justify-center text-zinc-500"
                                        title="Lihat Kartu Pendaftaran">
                                        <i class="bi bi-qr-code text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-400">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 flex items-center justify-center mx-auto mb-2 text-zinc-400 text-xl">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <span class="font-bold text-xs">Belum ada data pendaftar murid baru untuk filter
                                    ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pendaftar->hasPages())
            <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
                {{ $pendaftar->links() }}
            </div>
        @endif
    </div>

    <!-- ================= MODAL SCAN BARCODE ================= -->
    <div id="modalScan"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="m3-glass-card w-full max-w-md p-6 relative">
            <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-xl bg-primary/10 text-primary dark:bg-primary-dark/20 dark:text-primary-dark flex items-center justify-center">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white">Scan Barcode / Nomor Pendaftaran</h3>
                </div>
                <button type="button" onclick="tutupModalScan()"
                    class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="space-y-4">
                <p class="text-xs text-zinc-600 dark:text-zinc-300 font-semibold">
                    Arahkan scanner barcode fisik Anda atau ketikkan nomor pendaftaran di bawah ini lalu tekan
                    <strong>Enter</strong>.
                </p>

                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Nomor Pendaftaran / Barcode Text
                    </label>
                    <div class="relative">
                        <input type="text" id="inputScanNomor" placeholder="SPMB-2026-0001"
                            class="m3-input-glass w-full !h-12 text-sm font-mono font-black uppercase tracking-wider pl-10">
                        <i
                            class="bi bi-upc-scan text-lg text-zinc-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="tutupModalScan()"
                        class="m3-btn-secondary h-10 px-4 text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" onclick="prosesScanNomor()"
                        class="m3-btn-primary h-10 px-5 text-xs font-black gap-1.5">
                        <i class="bi bi-search"></i>
                        <span>Cari & Verifikasi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL VERIFIKASI & TERBITKAN NISM ================= -->
    <div id="modalVerifikasi"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="m3-glass-card w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-zinc-900 dark:text-white">Verifikasi Penerimaan Murid</h3>
                        <span class="text-[10px] font-mono text-zinc-400" id="labelVerifNoReg">No. Reg: -</span>
                    </div>
                </div>
                <button type="button" onclick="tutupModalVerifikasi()"
                    class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="formVerifikasiSubmit" method="POST" class="space-y-4">
                @csrf

                <!-- Ringkasan Calon Murid -->
                <div
                    class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-[10px] text-zinc-400 font-bold block">Nama Calon Murid:</span>
                        <span class="font-black text-zinc-900 dark:text-white uppercase" id="valNamaMurid">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 font-bold block">Jenjang Masuk:</span>
                        <span class="font-bold text-primary dark:text-primary-dark" id="valLevelMurid">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 font-bold block">Wali / No KK:</span>
                        <span class="font-bold text-zinc-800 dark:text-zinc-200" id="valWaliMurid">-</span>
                    </div>
                </div>

                <!-- Input Penerbitan NISM -->
                <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <label
                            class="text-[11px] font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-person-badge-fill"></i>
                            Nomor Induk Murid Madrasah (NISM) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-zinc-400">Nomor urut otomatis</span>
                    </div>
                    <input type="text" name="nism" id="inputNism"
                        class="m3-input-glass font-mono text-sm font-black text-emerald-700 dark:text-emerald-400 w-full"
                        required>
                    <p class="text-[10px] text-zinc-500 font-semibold">
                        Sistem telah mengisi usulan NISM berikutnya. Anda dapat mengubahnya jika ada penomoran khusus.
                    </p>
                </div>

                <!-- Pilihan Ruangan / Kelas Masuk -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                            Penempatan Ruangan Kelas Awal (Opsional)
                        </label>
                        <select name="ruangan_masuk" id="selectRuanganMasuk"
                            class="m3-input-glass w-full text-xs font-bold">
                            <option value="">-- Tentukan Nanti (Rombel) --</option>
                            @foreach ($ruangans as $r)
                                <option value="{{ $r->id }}">{{ $r->nama_ruangan }}
                                    ({{ $r->level->nama_level ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                            Status Pembayaran Tagihan Masuk
                        </label>
                        <select name="status_pembayaran" id="selectStatusBayar"
                            class="m3-input-glass w-full text-xs font-bold">
                            <option value="Belum Lunas">Belum Lunas</option>
                            <option value="Lunas">Lunas (Sudah Membayar)</option>
                            <option value="Gratis">Bebas / Gratis</option>
                        </select>
                    </div>
                </div>

                <!-- Opsi Penerbitan Tagihan SPMB -->
                <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="buat_tagihan" value="1" checked
                            class="mt-0.5 rounded text-primary focus:ring-primary">
                        <div class="text-xs">
                            <span class="font-black text-zinc-900 dark:text-white block">Terbitkan Tagihan Masuk SPMB
                                di Keuangan</span>
                            <span class="text-[11px] text-zinc-500 block mt-0.5">
                                Tagihan SPMB/Pendaftaran sesuai pengaturan tagihan tahun pelajaran ini akan otomatis
                                masuk ke daftar tagihan murid.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Catatan Admin -->
                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Catatan Admin / Keterangan Tambahan
                    </label>
                    <textarea name="catatan_admin" rows="2"
                        placeholder="Catatan berkas, nomor kwitansi, atau lainnya (opsional)..."
                        class="m3-input-glass w-full text-xs font-semibold"></textarea>
                </div>

                <!-- Tombol Submit Verifikasi -->
                <div class="flex justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="tutupModalVerifikasi()"
                        class="m3-btn-secondary h-10 px-4 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="m3-btn-primary h-10 px-6 text-xs font-black gap-1.5">
                        <i class="bi bi-check2-all"></i>
                        <span>Terima & Terbitkan NISM</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL TOLAK PENDAFTARAN ================= -->
    <div id="modalTolak"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="m3-glass-card w-full max-w-md p-6 relative">
            <div class="flex items-center justify-between mb-4 border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 flex items-center justify-center">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <h3 class="text-sm font-black text-zinc-900 dark:text-white">Tolak Pendaftaran Murid</h3>
                </div>
                <button type="button" onclick="tutupModalTolak()"
                    class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form id="formTolakSubmit" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-zinc-600 dark:text-zinc-300 font-semibold">
                    Apakah Anda yakin ingin menolak pendaftaran murid <strong id="namaMuridTolak">-</strong>?
                </p>

                <div>
                    <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-wider mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan_admin" rows="3" required
                        placeholder="Contoh: Berkas tidak lengkap, usia belum mencukupi..."
                        class="m3-input-glass w-full text-xs font-semibold"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="tutupModalTolak()"
                        class="m3-btn-secondary h-10 px-4 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit"
                        class="m3-btn-primary !bg-rose-600 hover:!bg-rose-700 h-10 px-5 text-xs font-black">
                        Tolak Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Modal Scan Barcode
            window.bukaModalScan = function() {
                const modal = document.getElementById('modalScan');
                if (modal) {
                    modal.classList.remove('hidden');
                    const input = document.getElementById('inputScanNomor');
                    if (input) {
                        input.value = '';
                        setTimeout(() => input.focus(), 100);
                    }
                }
            };

            window.tutupModalScan = function() {
                const modal = document.getElementById('modalScan');
                if (modal) modal.classList.add('hidden');
            };

            const inputScan = document.getElementById('inputScanNomor');
            if (inputScan) {
                inputScan.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        prosesScanNomor();
                    }
                });
            }

            window.prosesScanNomor = function() {
                const input = document.getElementById('inputScanNomor');
                if (!input) return;
                const nomor = input.value.trim();
                if (!nomor) return;

                fetch(`{{ url('spmb-admin/scan') }}/${encodeURIComponent(nomor)}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            tutupModalScan();
                            bukaModalVerifikasi(res.data.id);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Tidak Ditemukan',
                                text: res.message || 'Nomor pendaftaran tidak valid.',
                                customClass: {
                                    confirmButton: 'm3-btn-primary h-10 px-6 text-xs'
                                }
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Ditemukan',
                            text: 'Pendaftaran dengan nomor barcode tersebut tidak ditemukan.',
                            customClass: {
                                confirmButton: 'm3-btn-primary h-10 px-6 text-xs'
                            }
                        });
                    });
            };

            // Modal Verifikasi & Terbitkan NISM
            window.bukaModalVerifikasi = function(id) {
                fetch(`{{ url('spmb-admin') }}/${id}/detail-json`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            const data = res.data;
                            document.getElementById('labelVerifNoReg').innerText = `No. Reg: ${data.nomor_pendaftaran}`;
                            document.getElementById('valNamaMurid').innerText = data.nama_lengkap;
                            document.getElementById('valLevelMurid').innerText = data.level ? data.level.nama_level :
                                '-';
                            document.getElementById('valWaliMurid').innerText = data.wali_murid ?
                                `${data.wali_murid.nama_kepala_keluarga} (${data.wali_murid.no_kk})` : '-';
                            document.getElementById('inputNism').value = res.suggested_nism;

                            document.getElementById('formVerifikasiSubmit').action =
                                `{{ url('spmb-admin') }}/${id}/verifikasi`;
                            document.getElementById('modalVerifikasi').classList.remove('hidden');
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching detail:', err);
                    });
            };

            window.tutupModalVerifikasi = function() {
                const modal = document.getElementById('modalVerifikasi');
                if (modal) modal.classList.add('hidden');
            };

            // Modal Tolak
            window.bukaModalTolak = function(id, nama) {
                const label = document.getElementById('namaMuridTolak');
                if (label) label.innerText = nama;
                const form = document.getElementById('formTolakSubmit');
                if (form) form.action = `{{ url('spmb-admin') }}/${id}/tolak`;
                const modal = document.getElementById('modalTolak');
                if (modal) modal.classList.remove('hidden');
            };

            window.tutupModalTolak = function() {
                const modal = document.getElementById('modalTolak');
                if (modal) modal.classList.add('hidden');
            };
        </script>
    @endpush
</x-app-layout>
