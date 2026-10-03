<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK_Panitia_IMNI_Pengajuan_Yudisium_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm 1cm;
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
            font-size: 11px;
            line-height: 1.4;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 1cm 1.2cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
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
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak SK Panitia (Ctrl+P)</span>
        </button>
    </div>

    <!-- HALAMAN 1: SURAT KEPUTUSAN PANITIA -->
    <div class="sheet-container">
        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[3px] border-double border-slate-800 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[75px] max-h-[85px] w-auto object-contain">
            </div>
            <div class="text-right leading-snug">
                <h1 class="m-0 text-sm md:text-base font-black text-indigo-700 underline uppercase tracking-wide">
                    PANITIA IMTIHAN NIHA'I (IMNI)
                </h1>
                <h2 class="m-0 text-xs md:text-sm font-extrabold uppercase tracking-wide text-zinc-900">
                    {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                </h2>
                <p class="mt-0.5 text-[10px] font-bold text-zinc-600 uppercase">
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H
                    ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                </p>
                <p class="text-[9px] font-medium text-zinc-500">
                    {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                </p>
            </div>
        </div>

        <!-- JUDUL SK PANITIA -->
        <div class="text-center mb-5">
            <h3 class="text-xs font-black uppercase tracking-wider text-zinc-900">
                SURAT KEPUTUSAN PANITIA PELAKSANA IMTIHAN NIHA'I (IMNI)
            </h3>
            <p class="text-[10px] font-bold uppercase text-zinc-600">
                {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
            </p>
            <p class="text-[10.5px] font-bold font-mono text-zinc-800 mt-1">
                Nomor: {{ $lulusList->first()?->nomor_sk_lulus ?? '01/SK-PANITIA/IMNI/MDT-HS/' . date('Y') }}
            </p>
            <p class="text-xs font-black uppercase text-zinc-900 mt-1.5 leading-snug">
                TENTANG<br>
                PENETAPAN REKAPITULASI HASIL EVALUASI DAN REKOMENDASI KELULUSAN PESERTA IMTIHAN NIHA'I (IMNI)<br>
                UNTUK DIAJUKAN PADA SIDANG YUDISIUM KELULUSAN<br>
                TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                {{ $selectedTahun->nama_masehi ?? '-' }} M
            </p>
        </div>

        <!-- ISI KONSIDERANS SK -->
        <div class="space-y-3 text-[10.5px] text-justify">
            <!-- Menimbang -->
            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">Menimbang</div>
                <div class="w-3 shrink-0">:</div>
                <div class="space-y-1.5 flex-1">
                    <p>a. Bahwa seluruh rangkaian pelaksanaan kegiatan Imtihan Niha'i (IMNI) Tahun Pelajaran
                        {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M
                        telah selesai diselenggarakan sesuai dengan petunjuk teknis dan tata tertib yang berlaku;</p>
                    <p>b. Bahwa rekapitulasi penilaian dan pengolahan nilai akhir ujian (teori, praktik, serta
                        akumulasi) telah diselesaikan secara objektif oleh Panitia Pelaksana IMNI;</p>
                    <p>c. Bahwa untuk mengajukan daftar nominatif rekomendasi hasil evaluasi peserta ujian kepada Sidang
                        Yudisium Dewan Guru dan Kepala Madrasah guna penetapan putusan final kelulusan, dipandang perlu
                        menerbitkan Surat Keputusan Panitia Pelaksana.</p>
                </div>
            </div>

            <!-- Mengingat -->
            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">Mengingat</div>
                <div class="w-3 shrink-0">:</div>
                <div class="space-y-1 flex-1">
                    <p>1. Anggaran Dasar dan Anggaran Rumah Tangga (AD/ART) MDT Hidayatus Shibyan;</p>
                    <p>2. Pedoman dan Petunjuk Teknis Pelaksanaan Imtihan Niha'i (IMNI) MDT Hidayatus Shibyan;</p>
                    <p>3. Ketentuan Standar Kriteria Ketuntasan Minimal (KKM) dan Sistem Evaluasi Akhir;</p>
                    <p>4. Surat Keputusan Kepala Madrasah tentang Penetapan Susunan Panitia Pelaksana IMNI Tahun
                        Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H.</p>
                </div>
            </div>

            <!-- Memperhatikan -->
            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">Memperhatikan</div>
                <div class="w-3 shrink-0">:</div>
                <div class="flex-1">
                    <p>Hasil rapat pleno panitia pelaksana terkait rekapitulasi nilai akhir ujian, rekam kehadiran,
                        serta tata tertib ujian para peserta Imtihan Niha'i (IMNI) Tahun Pelajaran
                        {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M.</p>
                </div>
            </div>

            <!-- MEMUTUSKAN -->
            <div class="text-center font-black tracking-widest my-2.5 uppercase text-xs">
                MEMUTUSKAN
            </div>

            <!-- Menetapkan -->
            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">Menetapkan</div>
                <div class="w-3 shrink-0">:</div>
                <div class="flex-1 font-bold uppercase">
                    KEPUTUSAN PANITIA PELAKSANA IMTIHAN NIHA'I TENTANG PENETAPAN REKAPITULASI HASIL EVALUASI DAN
                    REKOMENDASI KELULUSAN PESERTA UJIAN UNTUK DIAJUKAN PADA SIDANG YUDISIUM.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">KESATU</div>
                <div class="w-3 shrink-0">:</div>
                <div class="flex-1">
                    Menetapkan Rekapitulasi Hasil Evaluasi Ujian dan Daftar Nama Murid Peserta IMNI yang dinyatakan
                    <strong>MEMENUHI SYARAT KELULUSAN (DIREKOMENDASIKAN)</strong> sebagaimana tercantum dalam Lampiran
                    Keputusan ini, dengan rincian: <strong>{{ $lulusList->count() }} Murid Direkomendasikan
                        Lulus</strong> dari total <strong>{{ $kelulusans->count() }} Murid Peserta Terdaftar</strong>.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">KEDUA</div>
                <div class="w-3 shrink-0">:</div>
                <div class="flex-1">
                    Mengajukan dan menyerahkan secara resmi hasil evaluasi dan daftar nominatif rekomendasi ini ke
                    <strong>Sidang Yudisium TIM Sembilan Madrasah Diniyah Takmiliyah Hidayatus Shibyan</strong> sebagai
                    bahan pertimbangan utama dalam penetapan keputusan final kelulusan serta penerbitan Ijazah /
                    Syahadah IMNI.
                </div>
            </div>

            <div class="flex gap-2">
                <div class="w-24 font-bold shrink-0">KETIGA</div>
                <div class="w-3 shrink-0">:</div>
                <div class="flex-1">
                    Keputusan Panitia ini berlaku sejak tanggal ditetapkan, dan apabila di kemudian hari terdapat
                    kekeliruan, akan diadakan perbaikan sebagaimana mestinya sebelum dimulainya Sidang Yudisium.
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN (KETUA PANITIA IMNI) -->
        <div class="mt-8 flex justify-end text-center text-[10.5px] break-inside-avoid">
            <div class="w-64">
                <p class="font-bold text-zinc-500 mb-1">
                    Ditetapkan di: {{ getSetting('kota_madrasah', 'Bangkalan') }}<br>
                    Pada tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </p>
                <p class="font-bold uppercase text-zinc-800">Ketua Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                    @elseif ($ketuaPanitia && $ketuaPanitia->ustadz && $ketuaPanitia->ustadz->signature_url)
                        <img src="{{ asset($ketuaPanitia->ustadz->signature_url) }}" alt="TTD Ketua Panitia"
                            class="h-14 object-contain">
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda
                            Tangan]</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase text-xs">
                    {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>
                @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                    <p class="text-[9px] text-zinc-500 font-mono">NIP: {{ $ketuaPanitia->ustadz->nip_ustadz }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- HALAMAN 2: LAMPIRAN DAFTAR REKOMENDASI MURID -->
    <div class="sheet-container page-break">
        <!-- Header Lampiran -->
        <div class="border-b-2 border-zinc-800 pb-2 mb-4 text-center">
            <h4 class="text-xs font-bold uppercase text-zinc-600">LAMPIRAN SURAT KEPUTUSAN PANITIA PELAKSANA IMNI</h4>
            <p class="text-[10px] font-mono text-zinc-500">Nomor:
                {{ $lulusList->first()?->nomor_sk_lulus ?? '01/SK-PANITIA/IMNI/MDT-HS/' . date('Y') }}</p>
            <p class="text-xs font-black uppercase text-zinc-900 mt-1">
                DAFTAR REKOMENDASI HASIL EVALUASI PESERTA IMTIHAN NIHA'I (IMNI) YANG DIAJUKAN KE SIDANG YUDISIUM<br>
                TAHUN PELAJARAN {{ $selectedTahun->nama_hijriyah ?? '-' }} H /
                {{ $selectedTahun->nama_masehi ?? '-' }} M
            </p>
        </div>

        <!-- TABEL LAMPIRAN -->
        <table class="w-full border-collapse border border-zinc-800 text-[10px] mb-4">
            <thead>
                <tr class="bg-zinc-100 text-zinc-900 font-black text-center uppercase tracking-wide">
                    <th class="border border-zinc-800 py-2 px-1 w-8">No</th>
                    <th class="border border-zinc-800 py-2 px-2 w-24">NISM</th>
                    <th class="border border-zinc-800 py-2 px-2 text-left">Nama Murid</th>
                    <th class="border border-zinc-800 py-2 px-1 w-8">L/P</th>
                    <th class="border border-zinc-800 py-2 px-2 w-24">Tingkat</th>
                    <th class="border border-zinc-800 py-2 px-1 w-20">Nilai Akhir</th>
                    <th class="border border-zinc-800 py-2 px-2 w-36">Rekomendasi Panitia</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lulusList as $index => $k)
                    <tr class="hover:bg-zinc-50">
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-mono font-bold text-center">
                            {{ $k->murid?->nism ?? '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 font-bold uppercase">
                            {{ $k->murid?->nama_lengkap }} </td>
                        <td class="border border-zinc-800 py-1.5 px-1 text-center font-bold">
                            {{ $k->murid?->jenis_kelamin }}</td>

                        <td class="border border-zinc-800 py-1.5 px-2 text-center">{{ $k->tingkat?->kode_tingkat }}
                        </td>
                        <td class="border border-zinc-800 py-1.5 px-1 font-mono font-bold text-center">
                            {{ number_format($k->nilai_akhir ?? $k->rata_nilai_teori, 2) }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 text-center font-bold text-emerald-800">
                            Direkomendasikan Lulus</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-zinc-800 py-6 text-center text-zinc-400 italic">
                            Belum ada data murid yang direkomendasikan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN PENGESAHAN LAMPIRAN (KETUA PANITIA IMNI) -->
        <div class="mt-6 flex justify-end text-center text-[10.5px] break-inside-avoid">
            <div class="w-64">
                <p class="font-bold text-zinc-500 mb-1">
                    {{ getSetting('kota_madrasah', 'Bangkalan') }},
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </p>
                <p class="font-bold uppercase text-zinc-800">Ketua Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                    @elseif ($ketuaPanitia && $ketuaPanitia->ustadz && $ketuaPanitia->ustadz->signature_url)
                        <img src="{{ asset($ketuaPanitia->ustadz->signature_url) }}" alt="TTD Ketua Panitia"
                            class="h-14 object-contain">
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda
                            Tangan]</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase text-xs">
                    {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>

            </div>
        </div>
    </div>

</body>

</html>
