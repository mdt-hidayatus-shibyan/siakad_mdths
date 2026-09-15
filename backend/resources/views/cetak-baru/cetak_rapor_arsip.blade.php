<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip_Rapor_{{ Str::slug($data['nama_murid']) }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* Pengaturan Standar Cetak A4 Satu Lembar */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        @media print {
            body {
                background-color: #fff;
                color: #000;
                font-size: 11px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }

            html,
            body {
                height: 99%;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            color: #1e293b;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        .border-none td,
        .border-none th {
            border: none !important;
            padding: 2px 0;
        }

        .signature-area {
            margin-top: 20px;
            font-size: 11px;
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body class="bg-white">

    <!-- TOMBOL CETAK & INDIKATOR ARSIP -->
    <div
        class="print:hidden sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b mb-6 no-print shadow-md">
        <div
            class="px-3 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            <i class="bi bi-shield-lock-fill mr-1.5 text-amber-400"></i> ARSIP DIGITAL RESMI
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95">
            <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
        </button>
    </div>

    @php
        // =======================================================================
        // LOGIKA DETEKSI STATUS & KELAS (Dijalankan di awal agar rapi)
        // =======================================================================

        $namaSemester = strtolower($data['semester'] ?? ($data['nama_semester'] ?? ''));
        $namaUjian = strtolower($data['nama_ujian'] ?? '');

        $isSemesterGenap =
            str_contains($namaSemester, '2') ||
            str_contains($namaSemester, 'genap') ||
            str_contains($namaUjian, 'imda 2') ||
            str_contains($namaUjian, 'imni');

        $levelNama = $data['nama_level'] ?? '';
        $tingkatNama = $data['nama_tingkat'] ?? 'MADRASAH';
        $isKelasAkhir = in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);

        // Ambil Data Kenaikan dari Arsip JSON (Ubah Array jadi Object)
        $riwayatKenaikan = null;
        if (isset($data['riwayat_kenaikan'])) {
            $riwayatKenaikan = (object) $data['riwayat_kenaikan'];
        }

        $statusKeputusan = $riwayatKenaikan->status_keputusan ?? null;
        $levelTujuanNama = $riwayatKenaikan->nama_level_tujuan ?? null;
        $catatanWali = $riwayatKenaikan->catatan_wali_kelas ?? null;
    @endphp

    <!-- KOP SURAT -->
    <div class="flex justify-between items-center border-b-[3px] border-double border-slate-700 pb-4 mb-5">
        <div class="flex items-center gap-4">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="w-[300px] h-auto object-contain">
        </div>
        <div class="text-right leading-snug">
            <h1 class="m-0 text-[17px] font-black text-blue-600 underline uppercase tracking-wide">RAPOR HASIL BELAJAR
            </h1>
            <h1 class="m-0 text-[17px] font-black text-blue-600 underline uppercase tracking-wide">Tingkat
                {{ strtoupper($tingkatNama) }}</h1>
            <p class="mt-0.5 text-xs font-bold text-slate-600 uppercase">{{ $data['semester'] ?? '-' }}</p>
            <p class="mt-0.5 text-[10px] font-bold text-slate-500 uppercase">Tahun Pelajaran
                {{ $data['tahun_pelajaran'] ?? '-' }}</p>
            <p class="mt-0.5 text-[10px] font-black">NO. RAPOR: {{ $data['nomor_dokumen'] ?? '-' }}</p>
        </div>
    </div>

    <!-- DATA MURID -->
    <table class="border-none w-full text-xs mb-5">
        <tr class="border-none">
            <td class="font-bold w-24 text-slate-600 uppercase text-[10px] tracking-wide align-top">Nama Murid</td>
            <td class="align-top w-2">:</td>
            <td class="font-black uppercase text-slate-900 text-sm align-top">{{ $data['nama_murid'] ?? '-' }}</td>

            <td class="font-bold w-28 text-slate-600 uppercase text-[10px] tracking-wide align-top pl-8">Ruangan</td>
            <td class="align-top w-2">:</td>
            <td class="font-bold text-slate-900 align-top">{{ $data['nama_ruangan'] ?? '-' }}</td>
        </tr>
        <tr class="border-none">
            <td class="font-bold text-slate-600 uppercase text-[10px] tracking-wide align-top">NISM / ID</td>
            <td class="align-top">:</td>
            <td class="font-bold text-slate-700 align-top">{{ $data['nism'] ?? '-' }}</td>

            <td class="font-bold text-slate-600 uppercase text-[10px] tracking-wide align-top pl-8">Jenis Ujian</td>
            <td class="align-top">:</td>
            <td class="font-bold text-slate-700 align-top">{{ $data['nama_ujian'] ?? '-' }}</td>
        </tr>
        <tr class="border-none">
            <td class="font-bold text-slate-600 uppercase text-[10px] tracking-wide align-top">Wali Murid</td>
            <td class="align-top">:</td>
            <td class="font-bold text-slate-700 align-top">{{ $data['nama_wali'] ?? '-' }}</td>

            <td class="font-bold text-slate-600 uppercase text-[10px] tracking-wide align-top pl-8">Kampung/Dusun</td>
            <td class="align-top">:</td>
            <td class="font-bold text-slate-700 align-top">{{ $data['nama_kampung'] ?? '-' }}</td>
        </tr>
    </table>

    <!-- MATRIKS NILAI -->
    <table class="text-xs mb-4">
        <thead>
            <tr class="bg-slate-100 text-center font-bold">
                <th class="w-10">No</th>
                <th>Mata Pelajaran</th>
                <th class="w-20">Nilai Angka</th>
                <th class="w-28">Kriteria Kelulusan</th>
                <th>Capaian Kompetensi / Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['matriks_nilai'] ?? [] as $index => $nilai)
                @php
                    $angka = round($nilai['nilai'] ?? 0);
                    $huruf = 'D';
                    $catatan = 'Perlu bimbingan khusus.';
                    if ($angka >= 90) {
                        $huruf = 'A';
                        $catatan = 'Sangat Baik dan Menguasai Materi.';
                    } elseif ($angka >= 80) {
                        $huruf = 'B';
                        $catatan = 'Baik dan Memahami Materi.';
                    } elseif ($angka >= 70) {
                        $huruf = 'C';
                        $catatan = 'Cukup, tingkatkan belajar.';
                    }
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="font-bold text-slate-800">{{ $nilai['mapel'] ?? '-' }}</td>
                    <td class="text-center font-black text-sm">{{ $angka }}</td>
                    <td class="text-center font-black text-sm">{{ $huruf }}</td>
                    <td class="text-slate-500 text-[11px]">{{ $catatan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-slate-400 ">Data arsip nilai kosong/korup.</td>
                </tr>
            @endforelse

            <tr class="bg-slate-50 font-bold">
                <td colspan="2" class="text-right pr-4 uppercase tracking-wider">Jumlah Nilai (Total)</td>
                <td class="text-center text-sm font-black">{{ $data['total_nilai'] ?? 0 }}</td>
                <td colspan="2" class="bg-white border-none"></td>
            </tr>
            <tr class="bg-slate-50 font-bold">
                <td colspan="2" class="text-right pr-4 uppercase tracking-wider">Rata-Rata Akademik</td>
                <td class="text-center text-sm font-black text-blue-600">{{ $data['rata_rata'] ?? 0 }}</td>
                <td colspan="2" class="bg-white border-none text-right pr-4 font-normal text-slate-500">
                    Peringkat Kelas: <strong class="text-slate-800 font-black">{{ $data['peringkat'] ?? '-' }}</strong>
                    dari {{ $data['dari_jumlah_murid'] ?? '-' }} Murid
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ABSENSI & KEDISIPLINAN -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <table class="text-[11px]">
            <thead>
                <tr class="bg-slate-100 font-bold">
                    <th colspan="2" class="p-1.5 text-left uppercase tracking-wider">Ketidakhadiran (Semester Ini)
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="w-2/3">Sakit (S)</td>
                    <td class="text-center font-bold">{{ $data['sakit'] ?? 0 }} Hari</td>
                </tr>
                <tr>
                    <td>Izin (I)</td>
                    <td class="text-center font-bold">{{ $data['izin'] ?? 0 }} Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan (Alpha)</td>
                    <td class="text-center font-bold {{ ($data['alpha'] ?? 0) > 0 ? 'text-rose-600' : '' }}">
                        {{ $data['alpha'] ?? 0 }} Hari
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="text-[11px]">
            <thead>
                <tr class="bg-slate-100 font-bold">
                    <th class="p-1.5 text-left uppercase tracking-wider">Catatan Kedisiplinan / Perilaku</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="h-[63px] align-top p-2 leading-relaxed">
                        @if (($data['total_pelanggaran'] ?? 0) > 0)
                            Terdapat {{ $data['total_pelanggaran'] }} catatan pelanggaran kedisiplinan. Mohon
                            tingkatkan ketaatan pada tata tertib madrasah.
                        @else
                            Sangat baik. Mematuhi seluruh tata tertib madrasah dengan disiplin. Pertahankan akhlakul
                            karimah.
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- KEPUTUSAN KENAIKAN / KELULUSAN (Hanya muncul di Semester Genap) -->
    @if ($isSemesterGenap)
        <div class="border-[2px] border-slate-800 p-3 mb-6 mt-4 text-xs">
            <p class="font-black text-slate-900 mb-1.5 underline tracking-wide">KEPUTUSAN PENGASUH MADRASAH SERTA KEPALA
                BIDANG TINGKAT {{ strtoupper($tingkatNama) }}:</p>

            @if ($riwayatKenaikan && $statusKeputusan)
                <p class="text-slate-700 leading-relaxed font-medium mb-3 text-justify">
                    Berdasarkan hasil pencapaian kompetensi, serta mempertimbangkan aspek kehadiran dan kedisiplinan
                    akhlak murid, maka ditetapkan:
                </p>

                <div class="flex flex-col gap-1.5 ml-4 mb-2">

                    @if ($isKelasAkhir)
                        <!-- TAMPILAN KHUSUS KELAS AKHIR (IMNI / LULUS) -->
                        @if ($statusKeputusan == 'Lulus')
                            <div class="flex items-center text-sm">
                                <span class="inline-block w-52 font-black uppercase text-blue-700 tracking-wider">STATUS
                                    PENDIDIKAN</span>
                                <span class="font-black text-blue-700">: LULUS DARI MADRASAH (TAMAT)</span>
                            </div>
                        @else
                            <div class="flex items-center text-sm">
                                <span class="inline-block w-52 font-black uppercase text-rose-700 tracking-wider">STATUS
                                    PENDIDIKAN</span>
                                <span class="font-black text-rose-700">: TIDAK LULUS (MENGULANG)</span>
                            </div>
                        @endif
                    @else
                        <!-- TAMPILAN STANDAR KELAS BIASA (NAIK / TINGGAL) -->
                        @if ($statusKeputusan == 'Naik Kelas')
                            <div class="flex items-center text-sm">
                                <span class="inline-block w-52 font-black uppercase text-emerald-700">NAIK KE TINGKAT /
                                    KELAS</span>
                                <span class="font-black text-slate-900">:
                                    {{ $levelTujuanNama ? strtoupper($levelTujuanNama) : '.......................................' }}</span>
                            </div>
                            <div class="flex items-center text-sm opacity-50">
                                <span class="inline-block w-52 font-bold uppercase text-slate-400 line-through">TINGGAL
                                    DI TINGKAT / KELAS</span>
                                <span class="font-bold text-slate-400 line-through">:
                                    {{ strtoupper($levelNama ?: '-') }}</span>
                            </div>
                        @elseif ($statusKeputusan == 'Tinggal Kelas')
                            <div class="flex items-center text-sm opacity-50">
                                <span class="inline-block w-52 font-bold uppercase text-slate-400 line-through">NAIK KE
                                    TINGKAT / KELAS</span>
                                <span class="font-bold text-slate-400 line-through">:
                                    ...........................................................</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="inline-block w-52 font-black uppercase text-rose-700">TINGGAL DI TINGKAT /
                                    KELAS</span>
                                <span class="font-black text-slate-900">:
                                    {{ $levelTujuanNama ? strtoupper($levelTujuanNama) : strtoupper($levelNama ?: '-') }}</span>
                            </div>
                        @endif
                    @endif

                </div>

                @if ($catatanWali)
                    <p class="mt-3 text-[11px] text-slate-800 font-bold bg-slate-50 p-2 border border-slate-200">
                        <i class="bi bi-chat-quote-fill mr-1 text-slate-400"></i> Catatan: {{ $catatanWali }}
                    </p>
                @endif
            @else
                <div class="p-3 bg-amber-50 border border-amber-200 text-amber-700 text-center font-bold text-sm">
                    <i class="bi bi-exclamation-triangle-fill mr-1"></i> Keputusan Kenaikan/Kelulusan Belum Ditetapkan
                    / Disahkan di Sistem.
                </div>
            @endif
        </div>
    @endif

    <!-- PENANDATANGAN BEKU (ARSIP) -->
    <table class="w-full text-center mt-6 text-[10pt] border-none" style="table-layout: fixed;">
        <tr>
            <td class="w-1/3 align-top" style="height: 70px;">
                <br>
                <p class="mb-1">Mengetahui,</p>
                <p class="font-bold">Pengasuh Madrasah</p>
            </td>
            <td class="w-1/3 align-top" style="height: 70px;">
                <br>
                <p class="mb-1">Mengetahui,</p>
                <p class="font-bold">Kepala Bidang Tingkat</p>
                <p class="font-bold">{{ strtoupper($data['kabid_tingkat'] ?? '-') }}</p>
            </td>
            <td class="w-1/3 align-top" style="height: 70px;">
                <p class="mb-0">Ditetapkan di : Bangkalan</p>
                <p class="mb-1">Pada Tanggal :
                    {{ isset($data['tanggal_disahkan']) ? \Carbon\Carbon::parse($data['tanggal_disahkan'])->translatedFormat('d F Y') : '-' }}
                </p>
                <p class="font-bold">Wali Ruangan {{ $data['nama_ruangan'] ?? '-' }}</p>
            </td>
        </tr>

        <tr>
            <!-- QR CODE 1: PENGASUH -->
            <td class="align-bottom pb-2 pt-2">
                <div class="flex justify-center">
                    @if (!empty($data['pengasuh_id']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                            URL::signedRoute('profil.publik', [
                                'tipe' => 'pengurus',
                                'id' => $data['pengasuh_id'],
                            ]),
                        ) !!}
                    @endif
                </div>
            </td>

            <!-- QR CODE 2: KABID -->
            <td class="align-bottom pb-2 pt-2">
                <div class="flex justify-center">
                    @if (!empty($data['kabid_id']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                            URL::signedRoute('profil.publik', [
                                'tipe' => 'pengurus',
                                'id' => $data['kabid_id'],
                            ]),
                        ) !!}
                    @endif
                </div>
            </td>

            <!-- QR CODE 3: WALI KELAS -->
            <td class="align-bottom pb-2 pt-2">
                <div class="flex justify-center">
                    @if (!empty($data['wali_ruangan_id']))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate(
                            URL::signedRoute('profil.publik', [
                                'tipe' => 'ustadz',
                                'id' => $data['wali_ruangan_id'],
                            ]),
                        ) !!}
                    @endif
                </div>
            </td>
        </tr>

        <tr>
            <td class="align-bottom">
                <p class="font-bold underline mb-0">{{ $data['pengasuh_nama'] ?? '-' }}</p>
            </td>
            <td class="align-bottom">
                <p class="font-bold underline mb-0">{{ $data['kabid_nama'] ?? '-' }}</p>
            </td>
            <td class="align-bottom">
                <p class="font-bold underline mb-0">{{ $data['wali_ruangan_nama'] ?? '-' }}</p>
            </td>
        </tr>
    </table>

    @if (request()->has('print') || request()->has('auto_print') || request()->has('download'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 600);
            });
        </script>
    @endif

</body>

</html>
