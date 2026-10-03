<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK_Hasil_Sidang_Yudisium_Kelulusan_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.6cm 0.8cm;
            }

            body {
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sheet-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                border: none !important;
            }

            .page-break {
                page-break-before: always;
                break-before: always;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.5;
            letter-spacing: 0.015em;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.7cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div
        class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('putusan-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak SK Yudisium (Ctrl+P)</span>
        </button>
    </div>

    <!-- HALAMAN 1: SURAT KEPUTUSAN SIDANG YUDISIUM MADRASAH -->
    <div class="sheet-container">
        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[2.5px] border-double border-slate-800 pb-2.5 mb-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[62px] max-h-[72px] w-auto object-contain">
            </div>
            <div class="text-right leading-snug">
                <h2 class="m-0 text-xs font-extrabold uppercase tracking-wider text-zinc-900">
                    MAJELIS SIDANG YUDISIUM KELULUSAN AKHIR
                </h2>
                <h1 class="m-0 text-sm font-black text-emerald-800 underline uppercase tracking-wider">
                    {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                </h1>
                <p class="mt-0.5 text-[9.5px] font-bold tracking-wide text-zinc-600 uppercase">
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H
                    | {{ $selectedTahun->nama_masehi ?? '-' }} M
                </p>

            </div>
        </div>

        <!-- JUDUL SK YUDISIUM -->
        <div class="text-center mb-2.5">
            <h3 class="text-[11px] font-black uppercase tracking-wider text-zinc-900">
                SURAT KEPUTUSAN SIDANG YUDISIUM MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN
            </h3>
            <p class="text-[9.5px] font-bold uppercase text-zinc-600">
                TENTANG PENETAPAN HASIL SIDANG YUDISIUM DAN KELULUSAN AKHIR MURID
            </p>
            <p class="text-[10px] font-bold font-mono text-zinc-800 mt-0.5">
                Nomor: {{ $lulusList->first()?->nomor_sk_lulus ?? '421.1/SK-YUDISIUM/MDT-HS/' . date('Y') }}
            </p>
            <p class="text-[10px] font-black uppercase text-zinc-900 mt-1 leading-tight">
                PENETAPAN KELULUSAN AKHIR MURID KELAS AKHIR (TINGKAT TPQ, IBTIDAIYAH, DAN TSANAWIYAH)<br>
                MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN<br>
                TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                {{ $selectedTahun->nama_masehi ?? '-' }} M
            </p>
        </div>

        <!-- ISI KONSIDERANS SK -->
        <div class="space-y-1.5 text-[9.5px] text-justify leading-tight">
            <!-- Menimbang -->
            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">Menimbang</div>
                <div class="w-2 shrink-0">:</div>
                <div class="space-y-1 flex-1">
                    <p>a. Bahwa seluruh proses pendidikan, pembelajaran, dan evaluasi akhir Imtihan Niha'i (IMNI) Tahun
                        Pelajaran
                        {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M
                        telah selesai dilaksanakan sesuai petunjuk teknis dan kurikulum yang berlaku;</p>
                    <p>b. Bahwa Panitia Pelaksana IMNI telah menyelesaikan rekapitulasi penilaian dan menyerahkan Surat
                        Keputusan Rekomendasi Kelulusan kepada Majelis Sidang Yudisium;</p>
                    <p>c. Bahwa berdasarkan hasil musyawarah mufakat pada Rapat Pleno Sidang Yudisium Pengasuh, Dewan
                        Tim Sembilan, Sekretaris Jenderal, dan Kepala Bidang Pendidikan, dipandang perlu menetapkan
                        Keputusan Sidang Yudisium tentang Kelulusan Akhir Murid Kelas Akhir MDT Hidayatus Shibyan Tahun
                        Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                        {{ $selectedTahun->nama_masehi ?? '-' }} M.</p>
                </div>
            </div>

            <!-- Mengingat -->
            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">Mengingat</div>
                <div class="w-2 shrink-0">:</div>
                <div class="space-y-0.5 flex-1">
                    <p>1. Anggaran Dasar dan Anggaran Rumah Tangga (AD/ART) MDT Hidayatus Shibyan;</p>
                    <p>2. Pedoman dan Standar Operasional Prosedur (SOP) Sidang Yudisium Kelulusan Akhir MDT Hidayatus
                        Shibyan;</p>
                    <p>3. Ketentuan Kriteria Ketuntasan Minimal (KKM), Rekapitulasi Presensi KBM & Kegiatan, serta
                        Catatan Kedisiplinan Murid;</p>
                    <p>4. Berita Acara Rapat Pleno Sidang Yudisium Kelulusan Tahun Pelajaran
                        {{ $selectedTahun->nama_hijriyah ?? '-' }} H.</p>
                </div>
            </div>

            <!-- Memperhatikan -->
            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">Memperhatikan</div>
                <div class="w-2 shrink-0">:</div>
                <div class="flex-1">
                    <p>1. Surat Keputusan Rekomendasi Kelulusan Panitia Pelaksana Imtihan Niha'i (IMNI) Tahun Pelajaran
                        {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M;</p>
                    <p class="mt-0.5">2. Hasil verifikasi kelayakan kelulusan, nilai kognitif (teori), praktik ibadah,
                        ujian Al-Qur'an, serta pertimbangan akhlak dan kehadiran oleh Majelis Dewan Tim Sembilan, para
                        Kepala
                        Bidang Pendidikan, dan Pengasuh MDT Hidayatus Shibyan.</p>
                </div>
            </div>

            <!-- MEMUTUSKAN -->
            <div class="text-center font-black tracking-widest my-1 uppercase text-[10.5px]">
                MEMUTUSKAN
            </div>

            <!-- Menetapkan -->
            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">Menetapkan</div>
                <div class="w-2 shrink-0">:</div>
                <div class="flex-1 font-bold uppercase">
                    KEPUTUSAN SIDANG YUDISIUM MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN TENTANG PENETAPAN HASIL
                    SIDANG YUDISIUM DAN KELULUSAN AKHIR MURID TAHUN PELAJARAN
                    {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">KESATU</div>
                <div class="w-2 shrink-0">:</div>
                <div class="flex-1">
                    Menetapkan nama-nama murid sebagaimana tercantum dalam Lampiran Keputusan ini dinyatakan
                    <strong>LULUS (TAMAT)</strong> dari jenjang pendidikannya pada Madrasah Diniyah Takmiliyah Hidayatus
                    Shibyan, dengan rincian: <strong>{{ $lulusList->count() }} Murid Dinyatakan Lulus</strong> dari
                    total <strong>{{ $kelulusans->count() }} Murid Peserta Sidang Yudisium</strong>.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">KEDUA</div>
                <div class="w-2 shrink-0">:</div>
                <div class="flex-1">
                    Kepada murid yang dinyatakan Lulus berhak menerima Ijazah / Syahadah Kelulusan, Transkrip Nilai
                    Akhir, Surat Keterangan Lulus (SKL), serta hak-hak akademis lainnya sesuai dengan jenjang tingkat
                    masing-masing.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-20 font-bold shrink-0">KETIGA</div>
                <div class="w-2 shrink-0">:</div>
                <div class="flex-1">
                    Keputusan Sidang Yudisium ini bersifat final, mutlak, dan berlaku sejak tanggal ditetapkan. Apabila
                    di kemudian hari terdapat kekeliruan dalam keputusan ini, akan diadakan perbaikan sebagaimana
                    mestinya.
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN MAJELIS SIDANG YUDISIUM (6 PIHAK DENGAN QR CODE) -->
        <div class="mt-3 pt-2 border-t border-zinc-300 break-inside-avoid">
            <div class="text-right text-[9px] text-zinc-500 font-bold mb-1.5">
                Ditetapkan di: {{ getSetting('kota_madrasah', 'Bangkalan') }},
                Pada tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </div>

            <!-- BARIS 2: MAJELIS SIDANG YUDISIUM (DEWAN TIM 9, SEKJEN, PENGASUH) -->
            <div class="grid grid-cols-3 gap-2 text-center text-[9.5px]  mb-2.5">
                <!-- 4. DEWAN TIM SEMBILAN -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Mengesahkan,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Dewan TIM Sembilan</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($dewanTimSembilan?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $dewanTimSembilan->id])) !!}
                        @elseif (!empty($dewanTimSembilan?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $dewanTimSembilan->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $dewanTimSembilan?->anggota?->nama_lengkap ?? ($dewanTimSembilan?->nama ?? 'Dewan Tim Sembilan') }}
                        </p>
                    </div>
                </div>

                <!-- 6. PENGASUH MADRASAH -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Mengesahkan,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Pengasuh Madrasah</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id])) !!}
                        @elseif (!empty($pengasuh?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $pengasuh->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan & Cap]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                        </p>
                    </div>
                </div>

                <!-- 5. SEKRETARIS JENDERAL -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Panitera Sidang,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Sekretaris Jenderal</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($sekjen?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $sekjen->id])) !!}
                        @elseif (!empty($sekjen?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $sekjen->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $sekjen?->anggota?->nama_lengkap ?? ($sekjen?->nama ?? 'Sekretaris Jenderal') }}
                        </p>
                    </div>
                </div>


            </div>
            <!-- BARIS 1: KEPALA BIDANG PENDIDIKAN (TPQ, IBT, TSA) -->
            <div class="grid grid-cols-3 gap-2 text-center text-[9.5px]">
                <!-- 1. KABID TPQ -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Mengetahui,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Kepala Bidang TPQ</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($kabidTpq?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabidTpq->id])) !!}
                        @elseif (!empty($kabidTpq?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kabidTpq->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $kabidTpq?->anggota?->nama_lengkap ?? ($kabidTpq?->nama ?? 'Kepala Bidang TPQ') }}
                        </p>
                    </div>
                </div>

                <!-- 2. KABID IBTIDAIYAH -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Mengetahui,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Kepala Bidang Ibtidaiyah</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($kabidIbt?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabidIbt->id])) !!}
                        @elseif (!empty($kabidIbt?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kabidIbt->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $kabidIbt?->anggota?->nama_lengkap ?? ($kabidIbt?->nama ?? 'Kepala Bidang Ibtidaiyah') }}
                        </p>
                    </div>
                </div>

                <!-- 3. KABID TSANAWIYAH -->
                <div class="flex flex-col justify-between min-h-[105px]">
                    <div>
                        <p class="text-[8.5px] text-zinc-500 mb-0.5">Mengetahui,</p>
                        <p class="font-bold uppercase text-zinc-800 leading-tight">Kepala Bidang Tsanawiyah</p>
                    </div>
                    <div class="my-1 flex items-center justify-center min-h-[46px]">
                        @if (!empty($kabidTsa?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabidTsa->id])) !!}
                        @elseif (!empty($kabidTsa?->anggota?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kabidTsa->anggota->ustadz_id])) !!}
                        @else
                            <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[8.5px]">[Tanda
                                Tangan]</div>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-zinc-900 underline uppercase text-[9.5px] leading-tight">
                            {{ $kabidTsa?->anggota?->nama_lengkap ?? ($kabidTsa?->nama ?? 'Kepala Bidang Tsanawiyah') }}
                        </p>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- HALAMAN 2+: LAMPIRAN HASIL SIDANG YUDISIUM PER RUANGAN ASAL -->
    @php
        $lulusByRuangan = $lulusList->groupBy(function ($k) {
            return $k->peserta?->ruangan_asal_id ?? 'level_' . ($k->level_id ?? 0);
        });
    @endphp

    @forelse ($lulusByRuangan as $groupKey => $groupMurid)
        @php
            $firstMurid = $groupMurid->first();
            $namaRuangan =
                $firstMurid?->peserta?->ruanganAsal?->nama_ruangan ?? ($firstMurid?->level?->nama_level ?? 'Umum');
            $namaTingkat = $firstMurid?->tingkat?->nama_tingkat ?? '-';
            $tingkatIdGroup = $firstMurid?->tingkat_id;
            $kabidRuangan = $semuaKabid->get($tingkatIdGroup) ?? ($kabidTingkat ?? $semuaKabid->first());
        @endphp
        <div class="sheet-container page-break">
            <!-- Header Lampiran -->
            <div class="border-b-2 border-zinc-800 pb-2 mb-4 text-center">
                <h4 class="text-xs font-bold uppercase text-zinc-600">LAMPIRAN SURAT KEPUTUSAN SIDANG YUDISIUM MDT
                    HIDAYATUS SHIBYAN</h4>
                <p class="text-[10px] font-mono text-zinc-500">Nomor:
                    {{ $firstMurid?->nomor_sk_lulus ?? ($lulusList->first()?->nomor_sk_lulus ?? '421.1/SK-YUDISIUM/MDT-HS/' . date('Y')) }}
                </p>
                <p class="text-xs font-black uppercase text-zinc-900 mt-1">
                    DAFTAR MURID YANG DINYATAKAN LULUS (TAMAT) PADA SIDANG YUDISIUM KELULUSAN AKHIR<br>
                    TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                    {{ $selectedTahun->nama_masehi ?? '-' }} M
                </p>
                <div
                    class="mt-2 inline-flex items-center gap-3 bg-emerald-50 text-emerald-950 px-3 py-1 rounded border border-emerald-300 text-[10.5px] font-bold">
                    <span>RUANGAN: <span
                            class="text-emerald-900 uppercase font-black">{{ $namaRuangan }}</span></span>
                    <span>•</span>
                    <span>TINGKAT: <span class="text-zinc-800 uppercase font-black">{{ $namaTingkat }}</span></span>
                    <span>•</span>
                    <span>JUMLAH: <span class="text-zinc-800 font-black">{{ $groupMurid->count() }} Murid
                            Lulus</span></span>
                </div>
            </div>

            <!-- TABEL LAMPIRAN -->
            <table class="w-full border-collapse border border-zinc-800 text-[10px] mb-4">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center uppercase tracking-wide">
                        <th class="border border-zinc-800 py-2 px-1 w-8">No</th>
                        <th class="border border-zinc-800 py-2 px-2 w-24">NISM</th>
                        <th class="border border-zinc-800 py-2 px-2 text-left">Nama Murid</th>
                        <th class="border border-zinc-800 py-2 px-2 w-8">L/P</th>
                        <th class="border border-zinc-800 py-2 px-2 w-24">Tingkat</th>
                        <th class="border border-zinc-800 py-2 px-1 w-20">Nilai Akhir</th>
                        <th class="border border-zinc-800 py-2 px-2 w-28">Hasil Yudisium</th>
                        <th class="border border-zinc-800 py-2 px-2 w-36">No. Ijazah / Syahadah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupMurid as $index => $k)
                        <tr class="hover:bg-zinc-50">
                            <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold">{{ $index + 1 }}
                            </td>
                            <td class="border border-zinc-800 py-1.5 px-2 font-mono font-bold text-center">
                                {{ $k->murid?->nism ?? '-' }}</td>
                            <td class="border border-zinc-800 py-1.5 px-2 font-bold uppercase">
                                {{ $k->murid?->nama_lengkap }}</td>
                            <td class="border border-zinc-800 py-1.5 px-2 font-mono text-center">
                                {{ $k->murid?->jenis_kelamin === 'L' ? 'L' : 'P' }}</td>
                            <td class="border border-zinc-800 py-1.5 px-2 text-center">
                                {{ $k->tingkat?->kode_tingkat }}
                            </td>
                            <td class="border border-zinc-800 py-1.5 px-1 font-mono font-bold text-center">
                                {{ number_format($k->nilai_akhir ?? $k->rata_nilai_teori, 2) }}</td>
                            <td
                                class="border border-zinc-800 py-1.5 px-2 text-center font-black text-emerald-800 uppercase">
                                LULUS
                            </td>
                            <td class="border border-zinc-800 py-1.5 px-2 font-mono text-center text-[9.5px]">
                                {{ $k->nomor_ijazah ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- TANDA TANGAN PENGESAHAN LAMPIRAN (4 PIHAK) -->
            <div class="mt-6 pt-2 border-t border-zinc-300 break-inside-avoid">
                <div class="text-right text-[10px] text-zinc-500 font-bold mb-2">
                    {{ getSetting('kota_madrasah', 'Bangkalan') }},
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </div>

                <div class="grid grid-cols-4 gap-2 text-center text-[10px]">
                    <!-- 1. DEWAN TIM SEMBILAN -->
                    <div class="flex flex-col justify-between min-h-[135px]">
                        <div>
                            <p class="text-[9px] text-zinc-500 mb-0.5">Mengesahkan,</p>
                            <p class="font-bold uppercase text-zinc-800 leading-tight">Dewan TIM Sembilan</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-h-[50px]">
                            @if (!empty($dewanTimSembilan?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $dewanTimSembilan->id])) !!}
                            @elseif (!empty($dewanTimSembilan?->anggota?->ustadz_id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $dewanTimSembilan->anggota->ustadz_id])) !!}
                            @else
                                <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[9px]">
                                    [Tanda Tangan]</div>
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-zinc-900 underline uppercase text-[10px] leading-tight">
                                {{ $dewanTimSembilan?->anggota?->nama_lengkap ?? ($dewanTimSembilan?->nama ?? 'Dewan Tim Sembilan') }}
                            </p>

                        </div>
                    </div>

                    <!-- 4. PENGASUH MADRASAH -->
                    <div class="flex flex-col justify-between min-h-[135px]">
                        <div>
                            <p class="text-[9px] text-zinc-500 mb-0.5">Mengesahkan,</p>
                            <p class="font-bold uppercase text-zinc-800 leading-tight">Pengasuh Madrasah</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-h-[50px]">
                            @if (!empty($pengasuh?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id])) !!}
                            @elseif (!empty($pengasuh?->anggota?->ustadz_id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $pengasuh->anggota->ustadz_id])) !!}
                            @else
                                <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[9px]">
                                    [Tanda Tangan & Cap]</div>
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-zinc-900 underline uppercase text-[10px] leading-tight">
                                {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                            </p>

                        </div>
                    </div>

                    <!-- 3. SEKRETARIS JENDERAL -->
                    <div class="flex flex-col justify-between min-h-[135px]">
                        <div>
                            <p class="text-[9px] text-zinc-500 mb-0.5">Panitera,</p>
                            <p class="font-bold uppercase text-zinc-800 leading-tight">Sekretaris Jenderal</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-h-[50px]">
                            @if (!empty($sekjen?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $sekjen->id])) !!}
                            @elseif (!empty($sekjen?->anggota?->ustadz_id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $sekjen->anggota->ustadz_id])) !!}
                            @else
                                <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[9px]">
                                    [Tanda Tangan]</div>
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-zinc-900 underline uppercase text-[10px] leading-tight">
                                {{ $sekjen?->anggota?->nama_lengkap ?? ($sekjen?->nama ?? 'Sekretaris Jenderal') }}
                            </p>

                        </div>
                    </div>

                    <!-- 2. KETUA BIDANG PENDIDIKAN TINGKAT -->
                    <div class="flex flex-col justify-between min-h-[135px]">
                        <div>
                            <p class="text-[9px] text-zinc-500 mb-0.5">Mengetahui,</p>
                            <p class="font-bold uppercase text-zinc-800 leading-tight">Kabid {{ $namaTingkat }}</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-h-[50px]">
                            @if (!empty($kabidRuangan?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kabidRuangan->id])) !!}
                            @elseif (!empty($kabidRuangan?->anggota?->ustadz_id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $kabidRuangan->anggota->ustadz_id])) !!}
                            @else
                                <div class="h-10 flex items-center justify-center text-zinc-300 italic text-[9px]">
                                    [Tanda Tangan]</div>
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-zinc-900 underline uppercase text-[10px] leading-tight">
                                {{ $kabidRuangan?->anggota?->nama_lengkap ?? ($kabidRuangan?->nama ?? 'Kabid Pendidikan') }}
                            </p>

                        </div>
                    </div>




                </div>
            </div>
        </div>
    @empty
        <div class="sheet-container page-break">
            <!-- Header Lampiran -->
            <div class="border-b-2 border-zinc-800 pb-2 mb-4 text-center">
                <h4 class="text-xs font-bold uppercase text-zinc-600">LAMPIRAN SURAT KEPUTUSAN SIDANG YUDISIUM MDT
                    HIDAYATUS SHIBYAN</h4>
                <p class="text-[10px] font-mono text-zinc-500">Nomor: 421.1/SK-YUDISIUM/MDT-HS/{{ date('Y') }}</p>
                <p class="text-xs font-black uppercase text-zinc-900 mt-1">
                    DAFTAR PESERTA DIDIK YANG DINYATAKAN LULUS (TAMAT) PADA SIDANG YUDISIUM KELULUSAN AKHIR<br>
                    TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                    {{ $selectedTahun->nama_masehi ?? '-' }} M
                </p>
            </div>

            <table class="w-full border-collapse border border-zinc-800 text-[10px] mb-4">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center uppercase tracking-wide">
                        <th class="border border-zinc-800 py-2 px-1 w-8">No</th>
                        <th class="border border-zinc-800 py-2 px-2 w-28">No. Peserta</th>
                        <th class="border border-zinc-800 py-2 px-2 text-left">Nama Murid</th>
                        <th class="border border-zinc-800 py-2 px-2 w-24">NISM</th>
                        <th class="border border-zinc-800 py-2 px-2 w-24">Tingkat</th>
                        <th class="border border-zinc-800 py-2 px-1 w-20">Nilai Akhir</th>
                        <th class="border border-zinc-800 py-2 px-2 w-28">Hasil Yudisium</th>
                        <th class="border border-zinc-800 py-2 px-2 w-36">No. Ijazah / Syahadah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="border border-zinc-800 py-6 text-center text-zinc-400 italic">
                            Belum ada data murid yang dinyatakan lulus pada sidang yudisium.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforelse

</body>

</html>
