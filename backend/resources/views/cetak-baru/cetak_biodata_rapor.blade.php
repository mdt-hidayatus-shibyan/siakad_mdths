<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata_Rapor_{{ Str::slug($murid->nama_lengkap) }}_{{ $murid->nism }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 14mm 10mm 14mm;
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
                display: none !important;
            }

            html,
            body {
                height: 99%;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            color: #0f172a;
            line-height: 1.4;
            background: #fff;
        }

        .table-biodata {
            width: 100%;
            border-collapse: collapse;
        }

        .table-biodata td {
            padding: 3px 4px;
            vertical-align: top;
        }
    </style>
</head>

<body class="bg-white">

    @php
        // LOGIKA FOTO MURID ROBUST
        $defaultFoto = asset('assets/' . ($murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png'));

        $fotoSrc = null;
        if (!empty($murid->foto)) {
            if (str_starts_with($murid->foto, 'http://') || str_starts_with($murid->foto, 'https://')) {
                $fotoSrc = $murid->foto;
            } elseif (str_starts_with($murid->foto, 'storage/')) {
                $fotoSrc = asset($murid->foto);
            } elseif (str_starts_with($murid->foto, 'assets/')) {
                $fotoSrc = asset($murid->foto);
            } else {
                $fotoSrc = asset('storage/' . $murid->foto);
            }
        } elseif ($murid->foto_url) {
            $fotoSrc = $murid->foto_url;
        } else {
            $fotoSrc = $defaultFoto;
        }
    @endphp

    <!-- TOMBOL AKSI CETAK (Hanya Muncul di Layar) -->
    <div
        class="print:hidden sticky top-0 z-50 flex flex-wrap justify-between items-center py-2.5 px-6 bg-slate-900 text-white gap-3 border-b mb-4 no-print shadow-md">
        <div class="flex items-center gap-2">
            <span
                class="px-2.5 py-1 bg-purple-500/20 text-purple-300 border border-purple-400/40 rounded-lg text-xs font-bold flex items-center">
                <i class="bi bi-file-earmark-person-fill mr-1.5 text-purple-400"></i> BIODATA RAPOR (HALAMAN DEPAN)
            </span>
            <span class="text-xs text-slate-400 hidden sm:inline">• {{ $murid->nama_lengkap }}
                ({{ $murid->nism }})</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.close()"
                class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-bold transition-all cursor-pointer">
                <i class="bi bi-x-lg mr-1"></i> Tutup
            </button>
            <button onclick="window.print()"
                class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
                <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- WRAPPER HALAMAN A4 -->
    <div class="max-w-[210mm] mx-auto px-2">

        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[3px] border-double border-slate-800 pb-2.5 mb-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[100px] w-auto object-contain">
            </div>

            <!-- QR Code NISM Dokumen -->
            <div class="pl-3  ml-3 flex flex-col items-center justify-center shrink-0">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->margin(0)->generate($murid->nism) !!}
                <span class="text-[8px] font-mono font-bold text-slate-500 mt-0.5">{{ $murid->nism }}</span>
            </div>
        </div>

        <!-- JUDUL LEMBAR DOKUMEN -->
        <div class="text-center my-2.5">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 underline underline-offset-4">
                BUKU LAPORAN HASIL BELAJAR (RAPOR)
            </h2>
            <h3 class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-800 mt-0.5">
                KETERANGAN TENTANG DIRI MURID (LEMBAR BIODATA)
            </h3>
        </div>

        <!-- TABEL RINCIAN BIODATA MURID -->
        <table class="table-biodata text-[11px] mb-3">
            <tbody>
                <!-- 1. NAMA LENGKAP -->
                <tr>
                    <td class="font-bold w-6 text-center">1.</td>
                    <td class="font-semibold w-48">Nama Murid</td>
                    <td class="w-2">:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. Nama Lengkap</td>
                    <td>:</td>
                    <td class="font-black uppercase text-slate-900 text-xs tracking-wide">
                        {{ $murid->nama_lengkap }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. Nama Panggilan</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">{{ $murid->nama_panggilan ?: '-' }}</td>
                </tr>

                <!-- 2. NOMOR INDUK -->
                <tr>
                    <td class="font-bold text-center">2.</td>
                    <td class="font-semibold">Nomor Induk</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. NISM (Nomor Induk Santri)</td>
                    <td>:</td>
                    <td class="font-black font-mono text-slate-900">{{ $murid->nism }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. NISN</td>
                    <td>:</td>
                    <td class="font-bold font-mono text-slate-800">{{ $murid->nisn ?: '-' }}</td>
                </tr>

                <!-- 3. NIK -->
                <tr>
                    <td class="font-bold text-center">3.</td>
                    <td class="font-semibold">Nomor Induk Kependudukan (NIK)</td>
                    <td>:</td>
                    <td class="font-bold font-mono text-slate-900">{{ $murid->nik ?: '-' }}</td>
                </tr>

                <!-- 4. JENIS KELAMIN -->
                <tr>
                    <td class="font-bold text-center">4.</td>
                    <td class="font-semibold">Jenis Kelamin</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">
                        {{ $murid->jenis_kelamin == 'L' ? 'Laki-laki (L)' : ($murid->jenis_kelamin == 'P' ? 'Perempuan (P)' : '-') }}
                    </td>
                </tr>

                <!-- 5. TEMPAT TANGGAL LAHIR -->
                <tr>
                    <td class="font-bold text-center">5.</td>
                    <td class="font-semibold">Tempat dan Tanggal Lahir</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">
                        {{ $murid->tempat_lahir ?: '-' }},
                        {{ $murid->tanggal_lahir ? \Carbon\Carbon::parse($murid->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>

                <!-- 6. AGAMA & KEWARGANEGARAAN -->
                <tr>
                    <td class="font-bold text-center">6.</td>
                    <td class="font-semibold">Agama & Kewarganegaraan</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">Islam / Indonesia (WNI)</td>
                </tr>

                <!-- 7. STATUS DALAM KELUARGA -->
                <tr>
                    <td class="font-bold text-center">7.</td>
                    <td class="font-semibold">Status dalam Keluarga</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. Anak Ke-</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">
                        {{ $murid->anak_ke ? $murid->anak_ke . ' (dari bersaudara)' : '-' }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. Hubungan Keluarga</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">{{ $murid->hub_kel ?: 'Anak Kandung' }}</td>
                </tr>

                <!-- 8. PENDIDIKAN MASUK / PENERIMAAN -->
                <tr>
                    <td class="font-bold text-center">8.</td>
                    <td class="font-semibold">Diterima di Madrasah ini</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. Pada Tanggal / Tahun Masuk</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">
                        @if ($murid->tahunMasuk)
                            Tahun Pelajaran {{ $murid->tahunMasuk->nama_hijriyah }} H /
                            {{ $murid->tahunMasuk->nama_masehi }} M
                        @elseif ($murid->created_at)
                            {{ $murid->created_at->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. Jenjang / Tingkat Masuk</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">
                        {{ $murid->levelMasuk->nama_level ?? ($murid->ruanganMasuk->level->nama_level ?? '-') }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">c. Di Ruangan / Kelas</td>
                    <td>:</td>
                    <td class="font-bold text-slate-800">
                        {{ $murid->ruanganMasuk->nama_ruangan ?? ($murid->nama_ruangan_aktif ?: '-') }}
                    </td>
                </tr>

                <!-- 9. DATA ORANG TUA / WALI -->
                <tr>
                    <td class="font-bold text-center">9.</td>
                    <td class="font-semibold">Data Orang Tua / Wali Murid</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. Nama Ayah Kandung</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">
                        {{ $murid->nama_ayah ?: '-' }}
                        @if ($murid->status_ayah)
                            <span class="text-[9.5px] font-normal text-slate-500">({{ $murid->status_ayah }})</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. Nama Ibu Kandung</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">
                        {{ $murid->nama_ibu ?: '-' }}
                        @if ($murid->status_ibu)
                            <span class="text-[9.5px] font-normal text-slate-500">({{ $murid->status_ibu }})</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">c. Nama Kepala Keluarga / Wali</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">
                        {{ $murid->waliMurid->nama_kepala_keluarga ?? ($murid->nama_ayah ?: '-') }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">d. Nomor Kartu Keluarga (No. KK)</td>
                    <td>:</td>
                    <td class="font-bold font-mono text-slate-800">{{ $murid->waliMurid->no_kk ?: '-' }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">e. Nomor Telepon / HP</td>
                    <td>:</td>
                    <td class="font-bold font-mono text-slate-800">{{ $murid->waliMurid->no_hp ?: '-' }}</td>
                </tr>

                <!-- 10. ALAMAT -->
                <tr>
                    <td class="font-bold text-center">10.</td>
                    <td class="font-semibold">Alamat Tempat Tinggal</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">a. Dusun / Kampung</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">{{ $murid->waliMurid->kampung->nama_kampung ?? '-' }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pl-4 text-slate-600 font-medium">b. Alamat Lengkap</td>
                    <td>:</td>
                    <td class="font-semibold text-slate-800 leading-relaxed">
                        {{ $murid->waliMurid->alamat_detail ?: 'Desa Somor Koneng, Kec. Kwanyar, Kab. Bangkalan 69163' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- AREA FOTO & TANDA TANGAN PENGASUH -->
        <div class="mt-3 pt-2.5 border-t border-slate-300 flex justify-between items-end">

            <!-- KOTAK PAS FOTO 3X4 -->
            <div class="pl-6 pb-1">
                <div
                    class="w-[30mm] h-[40mm] border-2 border-slate-700 rounded-lg flex flex-col items-center justify-center p-0.5 bg-slate-50 overflow-hidden relative shadow-xs">
                    <img src="{{ $fotoSrc }}" alt="Foto {{ $murid->nama_lengkap }}"
                        class="w-full h-full object-cover rounded-[5px]"
                        onerror="this.onerror=null; this.src='{{ $defaultFoto }}';">
                </div>
            </div>

            <!-- TANDA TANGAN & PENGESAHAN PENGASUH VIA QR CODE -->
            <div class="text-center w-72 pr-4">
                <p class="text-xs text-slate-700">
                    Bangkalan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </p>
                <p class="text-xs font-black text-slate-900 uppercase tracking-wide mt-0.5 mb-1.5">
                    Pengasuh Madrasah,
                </p>

                <!-- QR CODE PENGASUH / VALIDASI RESMI -->
                <div class="flex justify-center my-1">
                    @if ($pengasuh)
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->margin(0)->generate(
                                URL::signedRoute('profil.publik', [
                                    'tipe' => 'pengurus',
                                    'id' => $pengasuh->id,
                                ]),
                            ) !!}
                    @else
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->margin(0)->generate(url('/')) !!}
                    @endif
                </div>

                <p class="text-xs font-black uppercase text-slate-900 underline tracking-wide mt-1.5 mb-0">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'K.H. ABDUL FATTAH') }}
                </p>
                <p class="text-[9.5px] text-slate-500 font-semibold mt-0.5">
                    MDT Hidayatus Shibyan
                </p>
            </div>

        </div>

    </div>

</body>

</html>
