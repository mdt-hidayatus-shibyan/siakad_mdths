@section('title', 'Master Kegiatan & Divisi Ikhtibar')

<x-app-layout>
    <!-- Header Section -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 relative z-10">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                Master Kegiatan & Divisi Ikhtibar
            </h2>
            <p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">
                Konfigurasi identitas perayaan tahunan, logo/stempel, penautan tagihan KK, dan pembagian divisi kerja.
            </p>
        </div>

        <div class="flex items-center gap-3 w-full lg:w-auto">
            <!-- Filter Dropdown Tahun Pelajaran -->
            <form action="{{ route('master-ikhtibar.index') }}" method="GET" class="w-full sm:w-auto">
                <div class="relative group/select w-full sm:w-64">
                    <select name="tahun_pelajaran_id" onchange="this.form.submit()"
                        class="m3-input-glass w-full appearance-none cursor-pointer !pr-9 text-xs font-bold">
                        @foreach ($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama_hijriyah }} H | {{ $tp->nama_masehi }} M
                                {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-400">
                        <i class="bi bi-chevron-down text-xs font-bold"></i>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 1. Form Identitas Kegiatan Ikhtibar -->
    <div class="p-5 md:p-6 rounded-2xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm mb-6 relative z-10">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight">
                        Identitas & Pengaturan Ikhtibar
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Informasi tema, edisi pelaksanaan ke-, tanggal penting, dan berkas identitas resmi.
                    </p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $kegiatan->status_badge_color }}">
                {{ $kegiatan->status_kegiatan }}
            </span>
        </div>

        <form id="form-identitas-kegiatan" action="{{ route('master-ikhtibar.update-identitas', $kegiatan->id) }}" method="POST"
            enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Nama Kegiatan -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Nama Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}"
                        placeholder="Contoh: Ikhtibar & Haflah Akhirussanah"
                        class="m3-input-glass w-full font-bold" required>
                </div>

                <!-- Ikhtibar Ke- -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Ikhtibar Ke- (Edisi)
                    </label>
                    <input type="text" name="ikhtibar_ke" value="{{ old('ikhtibar_ke', $kegiatan->ikhtibar_ke) }}"
                        placeholder="Contoh: 52 Th, 53 Th"
                        class="m3-input-glass w-full font-bold text-primary dark:text-primary-light">
                </div>

                <!-- Status Kegiatan -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Status Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_kegiatan" class="m3-input-glass w-full font-bold">
                        <option value="Persiapan" {{ old('status_kegiatan', $kegiatan->status_kegiatan) === 'Persiapan' ? 'selected' : '' }}>Persiapan</option>
                        <option value="Berjalan" {{ old('status_kegiatan', $kegiatan->status_kegiatan) === 'Berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="Selesai" {{ old('status_kegiatan', $kegiatan->status_kegiatan) === 'Selesai' ? 'selected' : '' }}>Selesai Pelaksanaan</option>
                        <option value="Dikunci" {{ old('status_kegiatan', $kegiatan->status_kegiatan) === 'Dikunci' ? 'selected' : '' }}>Dikunci (Arsip Finansial)</option>
                    </select>
                </div>
            </div>

            <!-- Tema Kegiatan -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                    Tema Ikhtibar
                </label>
                <input type="text" name="tema_kegiatan" value="{{ old('tema_kegiatan', $kegiatan->tema_kegiatan) }}"
                    placeholder="Contoh: Meneguhkan Karakter Santri Berakhlakul Karimah Menyongsong Era Modern"
                    class="m3-input-glass w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tanggal Mulai -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Tanggal Mulai
                    </label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai?->format('Y-m-d')) }}"
                        class="m3-input-glass w-full">
                    <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i class="bi bi-arrow-repeat"></i> Sinkron ke Kalender
                    </p>
                </div>

                <!-- Tanggal Selesai -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Tanggal Selesai
                    </label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}"
                        class="m3-input-glass w-full">
                    <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i class="bi bi-arrow-repeat"></i> Sinkron ke Kalender
                    </p>
                </div>

                <!-- Tanggal Puncak Acara -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Puncak Haflah Akhirussanah
                    </label>
                    <input type="date" name="tanggal_puncak_acara" value="{{ old('tanggal_puncak_acara', $kegiatan->tanggal_puncak_acara?->format('Y-m-d')) }}"
                        class="m3-input-glass w-full">
                </div>

                <!-- Tanggal Hijriyah -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Tanggal Hijriyah
                    </label>
                    <input type="text" name="tanggal_hijriyah" value="{{ old('tanggal_hijriyah', $kegiatan->tanggal_hijriyah) }}"
                        placeholder="Contoh: 20-25 Sya'ban 1447 H"
                        class="m3-input-glass w-full">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Lokasi Kegiatan -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Lokasi / Tempat Pelaksanaan
                    </label>
                    <input type="text" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan', $kegiatan->lokasi_kegiatan) }}"
                        placeholder="Contoh: Halaman Utama MDT Hidayatus Shibyan"
                        class="m3-input-glass w-full">
                </div>

                <!-- Nomor SK Kepanitiaan -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Nomor SK Panitia
                    </label>
                    <input type="text" name="no_sk_panitia" value="{{ old('no_sk_panitia', $kegiatan->no_sk_panitia) }}"
                        placeholder="Contoh: SK/045/MDT-HS/III/2026"
                        class="m3-input-glass w-full">
                </div>

                <!-- Sumber Tagihan KK (Wali Murid) -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">
                        Tautkan Pengaturan Tagihan KK
                    </label>
                    <select name="pengaturan_tagihan_id" class="m3-input-glass w-full">
                        <option value="">-- Pilih Master Tagihan KK --</option>
                        @foreach ($pengaturanTagihans as $pt)
                            <option value="{{ $pt->id }}" {{ old('pengaturan_tagihan_id', $kegiatan->pengaturan_tagihan_id) == $pt->id ? 'selected' : '' }}>
                                [{{ $pt->kode_tagihan }}] {{ $pt->nama_tagihan }} - Rp {{ number_format($pt->nominal, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Upload Logo, Kop & Stempel -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <!-- Logo Kegiatan -->
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0">
                        @if ($kegiatan->logo_kegiatan)
                            <img src="{{ $kegiatan->logo_url }}" alt="Logo" class="w-full h-full object-contain">
                        @else
                            <i class="bi bi-image text-zinc-400 text-lg"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 text-[11px]">
                            Logo Resmi Ikhtibar (PNG/JPG Max 2MB)
                        </label>
                        <input type="file" name="logo_kegiatan" accept="image/*" class="text-[11px] text-zinc-500 mt-1 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary">
                    </div>
                </div>

                <!-- Kop Logo Ikhtibar -->
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0">
                        @if ($kegiatan->kop_logo_ikhtibar)
                            <img src="{{ $kegiatan->kop_logo_url }}" alt="Kop Logo" class="w-full h-full object-contain">
                        @else
                            <i class="bi bi-file-earmark-richtext text-zinc-400 text-lg"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 text-[11px]">
                            Kop Logo Surat & Kwitansi (PNG/JPG Max 2MB)
                        </label>
                        <input type="file" name="kop_logo_ikhtibar" accept="image/*" class="text-[11px] text-zinc-500 mt-1 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary">
                    </div>
                </div>

                <!-- Stempel Kegiatan -->
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center overflow-hidden shrink-0">
                        @if ($kegiatan->stempel_kegiatan)
                            <img src="{{ $kegiatan->stempel_url }}" alt="Stempel" class="w-full h-full object-contain">
                        @else
                            <i class="bi bi-award text-zinc-400 text-lg"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 text-[11px]">
                            Stempel Panitia Ikhtibar (PNG Transparan)
                        </label>
                        <input type="file" name="stempel_kegiatan" accept="image/*" class="text-[11px] text-zinc-500 mt-1 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary">
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan Identitas -->
            <div class="flex justify-end pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <button type="submit" class="m3-btn-primary text-xs">
                    <i class="bi bi-save2 text-sm"></i>
                    <span>Simpan Perubahan Identitas</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Action Bar Manajemen Divisi -->
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 relative z-10">
        <div>
            <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight">
                Struktur Divisi & Alokasi Pagu
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Kelola divisi kepanitiaan atau gunakan template divisi standar madrasah.
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Tombol Generate Struktur Standar (BPH + 13 Divisi) -->
            <button type="button" onclick="generateDivisiStandar()"
                class="m3-btn-secondary text-xs flex items-center gap-1.5 w-full sm:w-auto justify-center">
                <i class="bi bi-magic text-sm text-amber-500"></i>
                <span>Generate Struktur &amp; Pagu (BPH + 13 Divisi)</span>
            </button>

            <!-- Tombol Tambah Divisi Manual -->
            <a href="{{ route('master-ikhtibar.create-divisi', $kegiatan->id) }}"
                class="m3-btn-primary text-xs action-modal flex items-center gap-1.5 w-full sm:w-auto justify-center"
                data-modal-size="lg">
                <i class="bi bi-plus-circle-fill text-sm"></i>
                <span>Tambah Pos / Divisi</span>
            </a>
        </div>
    </div>

    <!-- Data Grid Divisi Container -->
    <div id="data-grid-container" class="relative z-10">
        @include('ikhtibar.master.list-divisi', ['divisis' => $divisis])
    </div>

    <!-- Script Khusus Generate Divisi Standar -->
    @push('scripts')
    <script>
        function generateDivisiStandar() {
            Swal.fire({
                title: 'Generate Struktur & 13 Divisi Standar?',
                text: 'Sistem akan otomatis membuat Pos BPH (Ketua, Sekretaris, Bendahara) dan 13 Divisi Operasional (Acara, Lomba, Juri, Gerak Jalan, Karnaval, Dokumentasi & Multimedia, Keamanan, Perlengkapan, Dekorasi, Konsumsi, Sound System, Wisuda, Humas) beserta estimasi pagu awal.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Buat Otomatis!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('{{ route('master-ikhtibar.generate-divisi-standar', $kegiatan->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            if (typeof window.refreshDataGrid === 'function') {
                                window.refreshDataGrid();
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal memproses permintaan.', 'error');
                    });
                }
            });
        }

        // Handler AJAX Submit untuk Form Identitas Kegiatan
        $('#form-identitas-kegiatan').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let originalHtml = submitBtn.html();
            let formData = new FormData(this);

            form.find('.error-ajax').remove();
            form.find('input, select, textarea').removeClass('border-red-500 focus:border-red-600 focus:ring-red-500/20');

            submitBtn.html('<i class="bi bi-arrow-repeat animate-spin text-sm mr-1.5"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    submitBtn.html(originalHtml).prop('disabled', false);
                    if (typeof Toast !== 'undefined') {
                        Toast.fire({
                            icon: 'success',
                            title: response.message || 'Identitas berhasil disimpan!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Identitas berhasil disimpan!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }

                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                },
                error: function(xhr) {
                    submitBtn.html(originalHtml).prop('disabled', false);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            let input = form.find('[name="' + field + '"]');
                            if (input.length) {
                                input.addClass('border-red-500 focus:border-red-600 focus:ring-red-500/20');
                                input.after('<p class="error-ajax text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> ' + messages[0] + '</p>');
                            }
                        });
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({
                                icon: 'error',
                                title: 'Mohon periksa data yang dimasukkan!'
                            });
                        }
                    } else {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                        Swal.fire('Error', msg, 'error');
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

