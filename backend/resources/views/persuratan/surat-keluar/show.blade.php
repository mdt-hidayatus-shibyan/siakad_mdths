@section('title', 'Detail Surat: ' . $surat->nomor_surat)

<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('surat-keluar.index') }}"
                class="w-10 h-10 bg-white/80 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all duration-200 shadow-sm active:scale-95 shrink-0 outline-none hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white"
                title="Kembali ke Daftar Surat">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2
                    class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">
                    Detail Surat Keluar
                </h2>
                <p
                    class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5 transition-colors duration-300">
                    Nomor: <span
                        class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $surat->nomor_surat }}</span> •
                    {{ $surat->perihal }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('surat-keluar.cetak', $surat->id) }}" target="_blank"
                class="m3-btn-primary h-10 px-4.5 group/btn shrink-0">
                <i class="bi bi-printer-fill text-sm"></i>
                <span>Cetak Surat</span>
            </a>
            <a href="{{ route('surat-keluar.duplicate', $surat->id) }}"
                class="px-4 py-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 border border-indigo-500/20 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs flex items-center gap-2 active:scale-95"
                title="Salin isi surat ini untuk murid/tujuan lain">
                <i class="bi bi-copy"></i>
                <span>Duplikasi</span>
            </a>
            <a href="{{ route('surat-keluar.edit', $surat->id) }}"
                class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/20 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs flex items-center gap-2 active:scale-95">
                <i class="bi bi-pencil-fill"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <!-- NOTIFIKASI -->
    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center gap-3 text-xs font-bold shadow-sm">
            <i class="bi bi-check-circle-fill text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $meta = $surat->meta_jenis;
        $badge = $surat->badge_status;
        $spesifik = $surat->isi_spesifik ?? [];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- KOLOM KIRI: METADATA & STATUS -->
        <div class="space-y-5">
            <!-- Kartu Metadata -->
            <div class="m3-glass-card p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-200/60 dark:border-zinc-800/60">
                    <span class="text-xs font-extrabold text-zinc-400 uppercase tracking-wider">Status Dokumen</span>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $badge['class'] }}">
                        <i class="bi {{ $badge['icon'] }}"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>

                <!-- Jenis Surat -->
                <div>
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Jenis / Template
                        Surat</span>
                    <div class="mt-1 flex items-center gap-2">
                        <span
                            class="px-2.5 py-1 rounded-xl text-xs font-extrabold border {{ $meta['badge_color'] }} flex items-center gap-1.5">
                            <i class="bi {{ $meta['icon'] }}"></i>
                            <span>{{ $meta['nama'] }}</span>
                        </span>
                    </div>
                </div>

                <!-- Tanggal Terbit -->
                <div>
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Tanggal
                        Terbit</span>
                    <div class="text-xs font-bold text-zinc-900 dark:text-white mt-1">
                        {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('l, d F Y') }}
                    </div>
                    @if ($surat->tanggal_hijriyah)
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                            {{ $surat->tanggal_hijriyah }}
                        </div>
                    @endif
                </div>

                <!-- Penerima -->
                <div>
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Tujuan /
                        Penerima</span>
                    <div class="text-xs font-bold text-zinc-900 dark:text-white mt-1">
                        {{ $surat->tujuan_surat }}
                    </div>
                    @if ($surat->alamat_tujuan)
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $surat->alamat_tujuan }}
                        </div>
                    @endif
                </div>

                <!-- Pejabat Penandatangan -->
                <div>
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Pejabat
                        Penandatangan</span>
                    <div class="text-xs font-bold text-zinc-900 dark:text-white mt-1">
                        {{ $surat->penandatangan_nama }}
                    </div>
                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                        {{ $surat->penandatangan_jabatan }}
                    </div>
                </div>

                <!-- QR Token Verifikasi -->
                <div class="pt-3 border-t border-zinc-200/60 dark:border-zinc-800/60">
                    <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Kode Unik
                        Verifikasi</span>
                    <div
                        class="font-mono text-[11px] font-bold text-zinc-600 dark:text-zinc-300 mt-1 bg-zinc-100 dark:bg-zinc-800 p-2 rounded-xl text-center select-all">
                        {{ $surat->qr_token }}
                    </div>
                </div>
            </div>

            <!-- Kartu Relasi Murid (Jika ada) -->
            @if ($surat->is_dispensasi_massal || (is_array($surat->murid_ids) && count($surat->murid_ids) > 1))
                <div class="m3-glass-card p-5">
                    <h4
                        class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><i class="bi bi-people-fill text-teal-500"></i> Daftar
                            Murid Terlampir</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">{{ count($surat->murid_dispensasi_list) }}
                            Murid</span>
                    </h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1 text-xs">
                        @foreach ($surat->murid_dispensasi_list as $idx => $m)
                            <div
                                class="p-2 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/70 dark:border-zinc-700/70">
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $idx + 1 }}.
                                    {{ $m['nama_lengkap'] ?? '-' }}</div>
                                <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400 mt-0.5 flex justify-between">
                                    <span>NISM: {{ $m['nism'] ?? '-' }}</span>
                                    <span>Kelas: <strong
                                            class="text-teal-600 dark:text-teal-400">{{ $m['kelas_lembaga'] ?? ($m['ruangan'] ?? '-') }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($surat->murid)
                <div class="m3-glass-card p-5">
                    <h4
                        class="font-black text-xs text-zinc-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="bi bi-person-badge-fill text-emerald-500"></i> Profil Murid Terhubung
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Nama Murid:</span>
                            <span
                                class="font-bold text-zinc-900 dark:text-white">{{ $surat->murid->nama_lengkap }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">NISM:</span>
                            <span class="font-bold font-mono">{{ $surat->murid->nism }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Kelas / Ruangan:</span>
                            <span class="font-bold">{{ $surat->murid->nama_ruangan_aktif }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Wali Murid:</span>
                            <span
                                class="font-bold">{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Dusun / Kampung:</span>
                            <span
                                class="font-bold">{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- KOLOM KANAN: PRATINJAU KERTAS SURAT RESMI -->
        <div class="lg:col-span-2">
            <div class="m3-glass-card p-6 sm:p-8 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-md">

                <!-- HEADER / KOP SIMULASI -->
                <div class="pb-1.5 border-b-2 border-black dark:border-white text-left">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah" class="object-contain"
                        style="max-width: 100%;
            max-height: 120px;
            height: auto;">
                </div>
                <div class="border-b border-black dark:border-white -mt-0.5 mb-3"></div>

                <!-- NOMOR & PERIHAL -->
                <div class="grid grid-cols-2 text-xs mb-4">
                    <div class="space-y-0.5">
                        <div class="flex"><span class="w-20 text-zinc-500">Nomor</span><span>:
                                <strong>{{ $surat->nomor_surat }}</strong></span></div>
                        <div class="flex"><span class="w-20 text-zinc-500">Sifat</span><span>:
                                {{ $surat->sifat_surat }}</span></div>
                        <div class="flex"><span class="w-20 text-zinc-500">Lampiran</span><span>:
                                {{ $surat->lampiran }}</span></div>
                        <div class="flex"><span class="w-20 text-zinc-500">Perihal</span><span>:
                                <strong>{{ $surat->perihal }}</strong></span></div>
                    </div>
                    <div class="text-right">
                        <div class="text-zinc-500 text-[11px]">{{ $surat->tempat_terbit }},
                            {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</div>
                        @if ($surat->tanggal_hijriyah)
                            <div class="text-zinc-400 text-[10.5px]">{{ $surat->tanggal_hijriyah }}</div>
                        @endif
                    </div>
                </div>

                <!-- KEPADA YTH -->
                <div class="text-xs mb-5">
                    <p>Kepada Yth.</p>
                    <p class="font-extrabold text-sm">{{ $surat->tujuan_surat }}</p>
                    <p class="text-zinc-500">{{ $surat->alamat_tujuan ?? 'Di Tempat' }}</p>
                </div>

                <!-- ISI SURAT PREVIEW -->
                <div class="text-xs space-y-3 leading-relaxed text-justify">
                    <p><em>Assalamualaikum War. Wab.</em></p>
                    <p>Dengan hormat,</p>
                    <p style="text-indent: 20px;">
                        Puji syukur Alhamdulillah kami ucapkan kehadirat Allah SWT yang telah melimpahkan rahmat dan
                        hidayah-Nya kepada kita semua. Sholawat dan salam tercurah kepada Nabi Muhammad SAW serta
                        keluarga dan para sahabatnya.
                    </p>

                    <!-- Konten Spesifik -->
                    @if ($surat->jenis_surat === 'surat_panggilan')
                        <p>Sehubungan dengan keperluan
                            <strong>{{ $spesifik['alasan_panggilan'] ?? 'evaluasi perkembangan dan tata tertib murid' }}</strong>,
                            maka dengan ini kami mengharap kehadiran Bapak/Ibu/Wali Murid dari:
                        </p>
                        @if ($surat->murid)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2 text-xs border border-zinc-200/60 dark:border-zinc-700/60">
                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-zinc-500">Nama Murid:</span>
                                    <span class="col-span-2 font-bold">{{ $surat->murid->nama_lengkap }}</span>
                                    <span class="text-zinc-500">NISM:</span>
                                    <span class="col-span-2 font-mono">{{ $surat->murid->nism ?? '-' }}</span>
                                    <span class="text-zinc-500">Kelas / Ruangan:</span>
                                    <span class="col-span-2">{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</span>
                                    <span class="text-zinc-500">Orang Tua / Wali:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}</span>
                                    <span class="text-zinc-500">Dusun / Kampung:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}</span>
                                </div>
                            </div>
                        @endif
                        <p class="mt-2">Untuk hadir pada:</p>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2">
                            <div class="flex"><span class="w-32 text-zinc-500">Hari / Tanggal</span><span>:
                                    <strong>{{ $spesifik['hari_panggilan'] ?? '-' }},
                                        {{ isset($spesifik['tanggal_panggilan']) && $spesifik['tanggal_panggilan'] ? \Carbon\Carbon::parse($spesifik['tanggal_panggilan'])->translatedFormat('d F Y') : '-' }}</strong></span>
                            </div>
                            <div class="flex"><span class="w-32 text-zinc-500">Waktu / Pukul</span><span>:
                                    {{ $spesifik['waktu_panggilan'] ?? '-' }}</span></div>
                            <div class="flex"><span class="w-32 text-zinc-500">Tempat</span><span>:
                                    {{ $spesifik['tempat_menghadap'] ?? '-' }}</span></div>
                            <div class="flex"><span class="w-32 text-zinc-500">Menghadap</span><span>:
                                    {{ $spesifik['menghadap_kepada'] ?? '-' }}</span></div>
                        </div>
                    @elseif ($surat->jenis_surat === 'surat_peringatan')
                        <p>Berdasarkan hasil evaluasi kedisiplinan dan tata tertib madrasah, dengan ini kami menerbitkan
                            <strong>{{ $spesifik['tingkat_sp'] ?? 'Surat Peringatan' }}</strong> kepada murid:
                        </p>
                        @if ($surat->murid)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2 text-xs border border-zinc-200/60 dark:border-zinc-700/60">
                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-zinc-500">Nama Murid:</span>
                                    <span class="col-span-2 font-bold">{{ $surat->murid->nama_lengkap }}</span>
                                    <span class="text-zinc-500">NISM:</span>
                                    <span class="col-span-2 font-mono">{{ $surat->murid->nism ?? '-' }}</span>
                                    <span class="text-zinc-500">Kelas / Ruangan:</span>
                                    <span class="col-span-2">{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</span>
                                    <span class="text-zinc-500">Orang Tua / Wali:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}</span>
                                    <span class="text-zinc-500">Dusun / Kampung:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}</span>
                                </div>
                            </div>
                        @endif
                        <p class="mt-2">Atas tindakan pelanggaran tata tertib sebagai berikut:</p>
                        <div
                            class="bg-rose-50 dark:bg-rose-950/20 p-3 rounded-xl border border-rose-200 dark:border-rose-900/40 my-2 space-y-1">
                            <p class="font-bold text-rose-700 dark:text-rose-300">
                                {{ $spesifik['bentuk_pelanggaran'] ?? '-' }}</p>
                            @if (!empty($spesifik['poin_tatib_dilanggar']))
                                <p class="text-[11px] text-zinc-500">Dasar Aturan:
                                    {{ $spesifik['poin_tatib_dilanggar'] }}</p>
                            @endif
                            @if (!empty($spesifik['tindakan_pembinaan']))
                                <p class="text-[11px] text-zinc-500">Tindakan Pembinaan:
                                    {{ $spesifik['tindakan_pembinaan'] }} (Masa:
                                    {{ $spesifik['batas_waktu_pembinaan'] ?? '-' }})</p>
                            @endif
                        </div>
                    @elseif ($surat->jenis_surat === 'surat_pemberitahuan')
                        <p>Dengan hormat, melalui surat ini kami sampaikan pemberitahuan mengenai
                            <strong>{{ $spesifik['pokok_pemberitahuan'] ?? $surat->perihal }}</strong>
                            @if ($surat->murid)
                                kepada Orang Tua / Wali dari murid:
                            @else
                                sebagai berikut:
                            @endif
                        </p>
                        @if ($surat->murid)
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2 text-xs border border-zinc-200/60 dark:border-zinc-700/60">
                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-zinc-500">Nama Murid:</span>
                                    <span class="col-span-2 font-bold">{{ $surat->murid->nama_lengkap }}</span>
                                    <span class="text-zinc-500">NISM:</span>
                                    <span class="col-span-2 font-mono">{{ $surat->murid->nism ?? '-' }}</span>
                                    <span class="text-zinc-500">Kelas / Ruangan:</span>
                                    <span class="col-span-2">{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</span>
                                    <span class="text-zinc-500">Orang Tua / Wali:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}</span>
                                    <span class="text-zinc-500">Dusun / Kampung:</span>
                                    <span
                                        class="col-span-2">{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 my-2">
                            <p class="font-semibold">{{ $spesifik['pokok_pemberitahuan'] ?? '-' }}</p>
                            @if (!empty($spesifik['jadwal_terkait']))
                                <p class="text-[11px] text-zinc-500">Jadwal/Waktu: {{ $spesifik['jadwal_terkait'] }}
                                </p>
                            @endif
                        </div>
                    @elseif ($surat->jenis_surat === 'surat_edaran')
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-2 my-2">
                            <p class="font-bold text-center uppercase">
                                {{ $spesifik['pokok_maklumat'] ?? $surat->perihal }}</p>
                            <div class="whitespace-pre-line text-xs font-mono pl-2">{!! nl2br(e($spesifik['instruksi_poin'] ?? '')) !!}</div>
                        </div>
                    @elseif ($surat->jenis_surat === 'surat_permohonan_izin')
                        <p>Sehubungan dengan pelaksanaan agenda
                            <strong>{{ $spesifik['nama_kegiatan'] ?? $surat->perihal }}</strong>, kami memohon izin
                            permohonan fasilitas/tempat dengan rincian:
                        </p>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 my-2">
                            <p><strong>Hari/Tgl:</strong> {{ $spesifik['hari_kegiatan'] ?? '' }},
                                {{ isset($spesifik['tanggal_kegiatan']) && $spesifik['tanggal_kegiatan'] ? \Carbon\Carbon::parse($spesifik['tanggal_kegiatan'])->translatedFormat('d F Y') : '' }}
                                ({{ $spesifik['waktu_kegiatan'] ?? '' }})</p>
                            <p><strong>Lokasi:</strong> {{ $spesifik['tempat_kegiatan'] ?? '' }}</p>
                            <p><strong>Permohonan:</strong> {{ $spesifik['fasilitas_dimohonkan'] ?? '' }}</p>
                        </div>
                    @elseif ($surat->jenis_surat === 'surat_dispensasi')
                        @if ($surat->is_dispensasi_massal)
                            <p>Sehubungan dengan adanya
                                <strong>{{ $spesifik['nama_kegiatan_dispensasi'] ?? 'kegiatan madrasah' }}</strong>
                                yang diadakan oleh MDT Hidayatus Shibyan, yang dilaksanakan pada:
                            </p>
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2 text-xs border border-zinc-200/60 dark:border-zinc-700/60">
                                @if (!empty($spesifik['hari_kegiatan_dispensasi']))
                                    <p><strong>Hari:</strong> {{ $spesifik['hari_kegiatan_dispensasi'] }}</p>
                                @endif
                                <p><strong>Tanggal:</strong>
                                    {{ isset($spesifik['tanggal_mulai_dispensasi']) && $spesifik['tanggal_mulai_dispensasi'] ? \Carbon\Carbon::parse($spesifik['tanggal_mulai_dispensasi'])->translatedFormat('d F Y') : '-' }}
                                    @if (
                                        !empty($spesifik['tanggal_selesai_dispensasi']) &&
                                            $spesifik['tanggal_selesai_dispensasi'] !== $spesifik['tanggal_mulai_dispensasi']
                                    )
                                        s.d.
                                        {{ \Carbon\Carbon::parse($spesifik['tanggal_selesai_dispensasi'])->translatedFormat('d F Y') }}
                                    @endif
                                </p>
                                @if (!empty($spesifik['waktu_kegiatan_dispensasi']))
                                    <p><strong>Waktu:</strong> {{ $spesifik['waktu_kegiatan_dispensasi'] }}</p>
                                @endif
                                <p><strong>Tempat:</strong>
                                    {{ $spesifik['tempat_kegiatan_dispensasi'] ?? 'MDT Hidayatus Shibyan' }}</p>
                            </div>
                            <p class="mt-2">
                                Maka kami selaku Kepala MDT Hidayatus Shibyan Desa Somorkoneng Kecamatan Kwanyar
                                Kabupaten Bangkalan mengajukan permohonan dispensasi untuk
                                <strong>{{ $spesifik['permohonan_dispensasi_khusus'] ?? 'dipulangkan lebih awal agar murid dapat mempersiapkan diri' }}</strong>.
                                Adapun Murid yang mengikuti
                                {{ $spesifik['nama_kegiatan_dispensasi'] ?? 'kegiatan tersebut' }} akan disebut
                                sebagaimana terlampir.
                            </p>
                        @else
                            {{-- DISPENSASI 1 MURID --}}
                            <p>Dengan ini kami menerangkan bahwa murid di bawah ini:</p>
                            @if ($surat->murid)
                                <div
                                    class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 font-medium my-2 text-xs border border-zinc-200/60 dark:border-zinc-700/60">
                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-zinc-500">Nama Murid:</span>
                                        <span class="col-span-2 font-bold">{{ $surat->murid->nama_lengkap }}</span>
                                        <span class="text-zinc-500">NISM:</span>
                                        <span class="col-span-2 font-mono">{{ $surat->murid->nism ?? '-' }}</span>
                                        <span class="text-zinc-500">Kelas / Ruangan:</span>
                                        <span class="col-span-2">{{ $surat->murid->nama_ruangan_aktif ?? '-' }}</span>
                                        <span class="text-zinc-500">Orang Tua / Wali:</span>
                                        <span
                                            class="col-span-2">{{ $surat->murid->nama_ayah ?: $surat->murid->waliMurid->nama_kepala_keluarga ?? ($surat->murid->nama_ibu ?? '-') }}</span>
                                        <span class="text-zinc-500">Dusun / Kampung:</span>
                                        <span
                                            class="col-span-2">{{ $surat->murid->waliMurid->kampung->nama_kampung ?? ($surat->murid->alamat ?? 'Somorkoneng') }}</span>
                                    </div>
                                </div>
                            @endif
                            <p class="mt-2">Diberikan izin dan dispensasi kehadiran karena mengikuti agenda kegiatan
                                <strong>{{ $spesifik['nama_kegiatan_dispensasi'] ?? $surat->perihal }}</strong> pada:
                            </p>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 my-2">
                                <p><strong>Periode Dispensasi:</strong>
                                    {{ isset($spesifik['tanggal_mulai_dispensasi']) && $spesifik['tanggal_mulai_dispensasi'] ? \Carbon\Carbon::parse($spesifik['tanggal_mulai_dispensasi'])->translatedFormat('d F Y') : '-' }}
                                    @if (
                                        !empty($spesifik['tanggal_selesai_dispensasi']) &&
                                            $spesifik['tanggal_selesai_dispensasi'] !== $spesifik['tanggal_mulai_dispensasi']
                                    )
                                        s.d.
                                        {{ \Carbon\Carbon::parse($spesifik['tanggal_selesai_dispensasi'])->translatedFormat('d F Y') }}
                                    @endif
                                    @if (!empty($spesifik['jumlah_hari']))
                                        ({{ $spesifik['jumlah_hari'] }})
                                    @endif
                                </p>
                                @if (!empty($spesifik['alasan_kegiatan']))
                                    <p><strong>Alasan:</strong> {{ $spesifik['alasan_kegiatan'] }}</p>
                                @endif
                            </div>
                        @endif
                    @elseif ($surat->jenis_surat === 'surat_undangan')
                        <p>Mengharap dengan hormat kehadiran Bapak/Ibu/Saudara pada kegiatan
                            <strong>{{ $spesifik['nama_acara'] ?? $surat->perihal }}</strong> yang insya Allah akan
                            dilaksanakan pada:
                        </p>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl space-y-1 my-2">
                            <p><strong>Hari/Tgl:</strong> {{ $spesifik['hari_acara'] ?? '' }},
                                {{ isset($spesifik['tanggal_acara']) && $spesifik['tanggal_acara'] ? \Carbon\Carbon::parse($spesifik['tanggal_acara'])->translatedFormat('d F Y') : '' }}
                            </p>
                            <p><strong>Waktu:</strong> {{ $spesifik['waktu_acara'] ?? '' }}</p>
                            <p><strong>Tempat:</strong> {{ $spesifik['tempat_acara'] ?? '' }}</p>
                            <p><strong>Pakaian:</strong> {{ $spesifik['pakaian_dresscode'] ?? '' }}</p>
                        </div>
                    @endif

                    @if ($surat->isi_surat)
                        <div class="mt-2 text-zinc-700 dark:text-zinc-300">
                            {!! nl2br(e($surat->isi_surat)) !!}
                        </div>
                    @endif

                    <p>Demikian surat ini kami susun. Atas perhatiannya dan kerja samanya kami menyampaikan terima kasih
                        dan mohon dimaklumi adanya.</p>
                    <p><em>Wassalamualaikum War. Wab.</em></p>
                </div>

                <!-- TANDA TANGAN & PENGESAHAN DOKUMEN (FORMAT SK ARSIP DENGAN QR CODE) -->
                @php
                    $activeSigners = $surat->penandatangan_aktif;
                    $signerCount = count($activeSigners);
                    $colWidth = $signerCount > 0 ? round(100 / $signerCount, 2) : 100;
                @endphp

                <div class="mt-8 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-center text-xs" style="table-layout: fixed; width: 100%;">
                        <tr>
                            @foreach ($activeSigners as $signer)
                                <td class="align-top pb-2" style="width: {{ $colWidth }}%;">
                                    @if (!empty($signer['label_atas']))
                                        <p class="mb-0.5 text-zinc-500 text-[11px]">{!! nl2br(e($signer['label_atas'])) !!}</p>
                                    @else
                                        <p class="mb-0.5 text-zinc-500 text-[11px]">
                                            {{ $signer['key'] === 'pengasuh' ? 'Mengesahkan,' : ($signer['key'] === 'admin' ? $surat->tempat_terbit . ', ' . \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : 'Mengetahui,') }}
                                        </p>
                                    @endif
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                                        {{ $signer['jabatan'] }}</p>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($activeSigners as $signer)
                                <td class="align-bottom pb-2 pt-2">
                                    <div class="flex justify-center items-center my-1">
                                        @if (!empty($signer['id_relasi']) && !empty($signer['tipe_relasi']))
                                            <div class="p-1.5 bg-white rounded-lg shadow-2xs inline-block">
                                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->generate(
                                                    URL::signedRoute('profil.publik', ['tipe' => $signer['tipe_relasi'], 'id' => $signer['id_relasi']]),
                                                ) !!}
                                            </div>
                                        @else
                                            <div class="p-1.5 bg-white rounded-lg shadow-2xs inline-block">
                                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->generate($surat->qr_token ?: 'MDTHS-VERIFIED') !!}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($activeSigners as $signer)
                                <td class="align-bottom pt-1">
                                    <p class="font-extrabold underline text-zinc-900 dark:text-zinc-100 text-xs mb-0">
                                        {{ $signer['nama'] ?? '-' }}</p>
                                    @if (!empty($signer['nip']) && $signer['nip'] !== '-')
                                        <p class="text-[10px] text-zinc-500 mt-0.5">NIP. {{ $signer['nip'] }}</p>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </table>

                    @if ($surat->tembusan)
                        <div class="mt-4 text-[10px] text-zinc-500">
                            <span class="font-bold">Tembusan:</span><br>
                            {!! nl2br(e($surat->tembusan)) !!}
                        </div>
                    @endif

                    <!-- KETERANGAN PENGESAHAN ELEKTRONIK (FORMAT SEPERTI REFERENSI) -->
                    <div class="mt-8 pt-3 border-t border-zinc-200 dark:border-zinc-700 flex items-center gap-3">
                        <div
                            class="shrink-0 bg-white p-1 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-2xs flex items-center justify-center">
                            @php
                                $primary = !empty($activeSigners) ? $activeSigners[0] : null;
                            @endphp
                            @if (!empty($primary['id_relasi']) && !empty($primary['tipe_relasi']))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => $primary['tipe_relasi'], 'id' => $primary['id_relasi']])) !!}
                            @else
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->margin(0)->generate($surat->qr_token ?: url('/')) !!}
                            @endif
                        </div>
                        <div class="text-[10px] text-zinc-600 dark:text-zinc-400 leading-relaxed text-justify flex-1">
                            Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT Hidayatus Shibyan
                            dan distempel digital resmi oleh Sistem Administrasi Persuratan MDTHS. Untuk verifikasi
                            keabsahan, kunjungi <a href="{{ url('/') }}" target="_blank"
                                class="underline text-emerald-600 dark:text-emerald-400 font-semibold">{{ url('/') }}</a>
                            dan masukkan nomor surat, atau scan QRCode di samping.
                        </div>
                    </div>
                    <div class="text-center text-[10px] text-zinc-400 mt-2 font-medium">
                        1 dari {{ $surat->total_halaman }}
                    </div>
                </div>

                {{-- HALAMAN 2 PREVIEW: LEMBAR LAMPIRAN --}}
                @if ($surat->has_lampiran)
                    <div
                        class="p-8 sm:p-12 border-t-4 border-dashed border-emerald-500/30 font-serif text-zinc-800 dark:text-zinc-200 text-xs sm:text-sm leading-relaxed space-y-4">

                        <!-- HEADER LAMPIRAN RESMI (KANAN ATAS) -->
                        <div class="flex justify-end text-[11px] leading-tight font-sans">
                            <table class="text-left">
                                <tr>
                                    <td class="font-bold pr-3 text-zinc-500">Lampiran</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-bold text-zinc-900 dark:text-white">
                                        {{ $surat->lampiran_judul }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-bold pr-3 text-zinc-500">Nomor</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-mono">{{ $surat->nomor_surat }}</td>
                                </tr>
                                <tr>
                                    <td class="font-bold pr-3 text-zinc-500">Tanggal</td>
                                    <td class="pr-2">:</td>
                                    <td>
                                        {{ $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : '-' }}
                                        @if ($surat->tanggal_hijriyah)
                                            / {{ $surat->tanggal_hijriyah }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-bold pr-3 text-zinc-500">Tentang</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-semibold">{{ $surat->perihal }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="border-b-2 border-zinc-900 dark:border-zinc-100 my-4"></div>

                        <!-- JUDUL LAMPIRAN -->
                        <div class="text-center my-4 font-sans">
                            <h3
                                class="font-black text-sm uppercase underline tracking-wide text-zinc-900 dark:text-white">
                                {{ $surat->lampiran_judul }}
                            </h3>
                        </div>

                        @if ($surat->is_dispensasi_massal)
                            <!-- TABEL DAFTAR NAMA MURID & KELAS LEMBAGA TUJUAN -->
                            <div class="overflow-x-auto my-4 font-sans">
                                <table
                                    class="w-full text-left text-xs border-collapse border border-zinc-900 dark:border-zinc-100">
                                    <thead>
                                        <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white">
                                            <th
                                                class="border border-zinc-900 dark:border-zinc-100 px-3 py-2 text-center w-12 font-bold">
                                                No.</th>
                                            <th
                                                class="border border-zinc-900 dark:border-zinc-100 px-4 py-2 font-bold">
                                                Nama Murid</th>
                                            <th
                                                class="border border-zinc-900 dark:border-zinc-100 px-4 py-2 text-center w-44 font-bold">
                                                Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-900 dark:divide-zinc-100">
                                        @foreach ($surat->murid_dispensasi_list as $idx => $m)
                                            <tr>
                                                <td
                                                    class="border border-zinc-900 dark:border-zinc-100 px-3 py-2 text-center">
                                                    {{ $idx + 1 }}</td>
                                                <td
                                                    class="border border-zinc-900 dark:border-zinc-100 px-4 py-2 font-bold uppercase text-zinc-900 dark:text-zinc-100">
                                                    {{ $m['nama_lengkap'] ?? '-' }}</td>
                                                <td
                                                    class="border border-zinc-900 dark:border-zinc-100 px-4 py-2 text-center font-semibold text-zinc-800 dark:text-zinc-200">
                                                    {{ $m['kelas_lembaga'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- ISI KONTEN LAMPIRAN -->
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs leading-relaxed whitespace-pre-line text-zinc-800 dark:text-zinc-200 font-sans">
                                {!! nl2br(e($surat->lampiran_konten)) !!}
                            </div>
                        @endif

                        <!-- TANDA TANGAN PENGESAHAN LAMPIRAN -->
                        <div class="pt-6">
                            <table class="w-full text-center text-xs" style="table-layout: fixed;">
                                <tr>
                                    @foreach ($activeSigners as $signer)
                                        <td class="align-top pb-2">
                                            <p class="text-[10px] text-zinc-500 mb-0.5">
                                                {{ $signer['label_atas'] ?? ($signer['key'] === 'pengasuh' ? 'Mengesahkan,' : 'Mengetahui,') }}
                                            </p>
                                            <p class="font-bold text-zinc-800 dark:text-zinc-200 mb-0">
                                                {{ $signer['jabatan'] }}</p>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($activeSigners as $signer)
                                        <td class="align-middle py-3">
                                            <div class="flex justify-center">
                                                @if (!empty($signer['id_relasi']) && !empty($signer['tipe_relasi']))
                                                    <div class="p-1.5 bg-white rounded-lg shadow-2xs inline-block">
                                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->generate(
                                                            URL::signedRoute('profil.publik', ['tipe' => $signer['tipe_relasi'], 'id' => $signer['id_relasi']]),
                                                        ) !!}
                                                    </div>
                                                @else
                                                    <div class="p-1.5 bg-white rounded-lg shadow-2xs inline-block">
                                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->generate($surat->qr_token ?: 'MDTHS-VERIFIED') !!}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($activeSigners as $signer)
                                        <td class="align-bottom pt-1">
                                            <p
                                                class="font-extrabold underline text-zinc-900 dark:text-zinc-100 text-xs mb-0">
                                                {{ $signer['nama'] ?? '-' }}</p>
                                            @if (!empty($signer['nip']) && $signer['nip'] !== '-')
                                                <p class="text-[10px] text-zinc-500 mt-0.5">NIP. {{ $signer['nip'] }}
                                                </p>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </div>

                        <!-- KETERANGAN PENGESAHAN ELEKTRONIK HALAMAN 2 -->
                        <div class="mt-8 pt-3 border-t border-zinc-200 dark:border-zinc-700 flex items-center gap-3">
                            <div
                                class="shrink-0 bg-white p-1 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-2xs flex items-center justify-center">
                                @if (!empty($primary['id_relasi']) && !empty($primary['tipe_relasi']))
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => $primary['tipe_relasi'], 'id' => $primary['id_relasi']])) !!}
                                @else
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->margin(0)->generate($surat->qr_token ?: url('/')) !!}
                                @endif
                            </div>
                            <div
                                class="text-[10px] text-zinc-600 dark:text-zinc-400 leading-relaxed text-justify flex-1">
                                Dokumen ini ditandatangani secara elektronik oleh Pejabat Berwenang MDT Hidayatus
                                Shibyan
                                dan distempel digital resmi oleh Sistem Administrasi Persuratan MDTHS. Untuk verifikasi
                                keabsahan, kunjungi <a href="{{ url('/') }}" target="_blank"
                                    class="underline text-emerald-600 dark:text-emerald-400 font-semibold">{{ url('/') }}</a>
                                dan masukkan nomor surat, atau scan QRCode di samping.
                            </div>
                        </div>
                        <div class="text-center text-[10px] text-zinc-400 mt-2 font-medium">
                            2 dari 2
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
