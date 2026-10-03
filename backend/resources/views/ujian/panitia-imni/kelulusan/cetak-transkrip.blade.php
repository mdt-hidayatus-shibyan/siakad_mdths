<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip_Nilai_IMNI_{{ Str::slug($peserta->murid?->nama_lengkap ?? 'murid') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            line-height: 1.35;
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
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('putusan-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Transkrip (Ctrl+P)</span>
        </button>
    </div>

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
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                </p>
                <p class="text-[9px] font-medium text-zinc-500">
                    {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                </p>
            </div>
        </div>

        <!-- JUDUL TRANSKRIP -->
        <div class="text-center mb-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 underline decoration-2 underline-offset-4">
                TRANSKRIP NILAI HASIL IMTIHAN NIHAI (IMNI)
            </h3>
            <p class="text-[10.5px] font-bold font-mono text-zinc-700 mt-1">
                Nomor Ijazah: {{ $peserta->kelulusan?->nomor_ijazah ?? 'IJZ-IMNI/' . date('Y') . '/' . $peserta->nomor_peserta }}
            </p>
        </div>

        <!-- BIODATA MURID -->
        <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-[10.5px] mb-4 p-3 bg-zinc-50 border border-zinc-200 rounded-xl">
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">Nama Lengkap Murid</span>
                <span class="w-3">:</span>
                <span class="font-black uppercase text-zinc-900">{{ $peserta->murid?->nama_lengkap }} ({{ $peserta->murid?->jenis_kelamin }})</span>
            </div>
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">Nomor Peserta IMNI</span>
                <span class="w-3">:</span>
                <span class="font-mono font-bold text-violet-700">{{ $peserta->nomor_peserta }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">NISM</span>
                <span class="w-3">:</span>
                <span class="font-mono font-semibold">{{ $peserta->murid?->nism ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">Jenjang / Tingkat</span>
                <span class="w-3">:</span>
                <span class="font-bold text-zinc-900">{{ $peserta->tingkat?->nama_tingkat }} ({{ $peserta->level?->nama_level }})</span>
            </div>
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">Tempat, Tgl Lahir</span>
                <span class="w-3">:</span>
                <span>{{ $peserta->murid?->tempat_lahir ?? '-' }}, {{ $peserta->murid?->tanggal_lahir ? \Carbon\Carbon::parse($peserta->murid->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-32 text-zinc-500 font-semibold">Tahun Pelajaran</span>
                <span class="w-3">:</span>
                <span class="font-semibold">{{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M</span>
            </div>
        </div>

        <!-- DAFTAR NILAI MATA PELAJARAN UJIAN IMNI -->
        <div class="mb-5">
            <div class="font-black text-[11px] text-zinc-900 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span>Daftar Nilai Mata Pelajaran Ujian IMNI</span>
            </div>

            <table class="w-full border-collapse border border-zinc-800 text-[10px]">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                        <th class="border border-zinc-800 py-1.5 px-1 w-8">No</th>
                        <th class="border border-zinc-800 py-1.5 px-3 text-left">Mata Pelajaran</th>
                        <th class="border border-zinc-800 py-1.5 px-2 w-16">KKM</th>
                        <th class="border border-zinc-800 py-1.5 px-2 w-20">Nilai Angka</th>
                        <th class="border border-zinc-800 py-1.5 px-2 w-24">Huruf / Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalTeori = 0;
                        $countTeori = 0;
                    @endphp
                    @forelse ($jadwals as $index => $j)
                        @php
                            $nObj = $nilaiTeori->get($j->id);
                            $val = $nObj ? (float) $nObj->nilai : null;
                            if ($val !== null) {
                                $totalTeori += $val;
                                $countTeori++;
                            }

                            $predikat = 'Kurang';
                            if ($val >= 90) $predikat = 'Amat Baik (A)';
                            elseif ($val >= 80) $predikat = 'Baik (B)';
                            elseif ($val >= 65) $predikat = 'Cukup (C)';
                        @endphp
                        <tr>
                            <td class="border border-zinc-800 py-1 px-1 text-center font-bold">{{ $index + 1 }}</td>
                            <td class="border border-zinc-800 py-1 px-3 font-semibold">{{ $j->nama_mapel }}</td>
                            <td class="border border-zinc-800 py-1 px-2 text-center font-mono font-semibold">65</td>
                            <td class="border border-zinc-800 py-1 px-2 text-center font-mono font-bold">{{ $val !== null ? number_format($val, 1) : '-' }}</td>
                            <td class="border border-zinc-800 py-1 px-2 text-center font-medium">{{ $val !== null ? $predikat : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border border-zinc-800 py-4 text-center text-zinc-400 italic">
                                Belum ada mata pelajaran terdaftar.
                            </td>
                        </tr>
                    @endforelse

                    @php
                        $rataTeori = $countTeori > 0 ? round($totalTeori / $countTeori, 2) : 0;
                    @endphp
                    <tr class="bg-zinc-50 font-bold border-t-2 border-zinc-800">
                        <td colspan="3" class="border border-zinc-800 py-1.5 px-3 text-right uppercase">Rata-Rata Nilai Ujian IMNI :</td>
                        <td class="border border-zinc-800 py-1.5 px-2 text-center font-mono font-black text-violet-800">{{ $rataTeori > 0 ? number_format($rataTeori, 2) : '-' }}</td>
                        <td class="border border-zinc-800 py-1.5 px-2 text-center text-[9.5px]">
                            {{ $rataTeori >= 65 ? 'Tuntas KKM' : 'Belum Tuntas' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- REKAPITULASI PUTUSAN KELULUSAN -->
        @php
            $kel = $peserta->kelulusan;
            $statusKel = $kel?->status_kelulusan ?? 'Lulus';
            $isLulus = in_array($statusKel, ['Lulus', 'Lulus Murni', 'Lulus Bersyarat']);
            $nilaiAkhir = $kel?->nilai_akhir ?? $rataTeori;
        @endphp
        <div class="p-3 bg-zinc-100 border-2 border-zinc-800 rounded-xl mb-6 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 block">HASIL KEPUTUSAN SIDANG YUDISIUM :</span>
                <span class="text-sm font-black uppercase tracking-wide text-zinc-900">
                    STATUS KELULUSAN: <span class="{{ $isLulus ? 'text-emerald-700' : 'text-rose-700' }}">{{ $isLulus ? 'LULUS' : $statusKel }}</span>
                </span>
                @if ($kel && $kel->skor_sem1 && $kel->skor_sem2)
                    <div class="text-[9.5px] text-zinc-600 font-semibold mt-0.5">
                        Skor Semester 1: {{ number_format($kel->skor_sem1, 2) }} &bull; Skor Semester 2: {{ number_format($kel->skor_sem2, 2) }}
                    </div>
                @endif
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-zinc-500 block">Nilai Akhir Akumulasi:</span>
                <span class="font-mono font-black text-base text-zinc-900">{{ number_format($nilaiAkhir, 2) }} / 100</span>
            </div>
        </div>

        <!-- TANDA TANGAN PENGESAHAN (KETUA PANITIA IMNI) -->
        <div class="flex justify-end text-center text-[10.5px] break-inside-avoid mt-6">
            <div class="w-64">
                <p class="font-bold text-zinc-500 mb-1">
                    {{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </p>
                <p class="font-bold uppercase text-zinc-800">Ketua Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                        ) !!}
                    @elseif ($ketuaPanitia && $ketuaPanitia->ustadz && $ketuaPanitia->ustadz->signature_url)
                        <img src="{{ asset($ketuaPanitia->ustadz->signature_url) }}" alt="TTD Ketua Panitia" class="h-14 object-contain">
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">[Tanda Tangan]</div>
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

</body>

</html>
