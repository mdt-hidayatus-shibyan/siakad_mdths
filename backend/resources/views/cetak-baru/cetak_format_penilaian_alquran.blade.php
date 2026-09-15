<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Lembar_Penilaian_Ujian_Alquran_{{ Str::slug($ujian->nama_ujian) }}_{{ $ruanganAktif->nama_ruangan ?? 'Semua' }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm;
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
                page-break-after: always;
                break-after: page;
            }

            tr {
                page-break-inside: avoid;
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
            padding: 0.8cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .kop-left img {
            max-height: 70px;
            width: auto;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.3;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9.5px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #475569;
            padding: 5px 5px;
            vertical-align: middle;
        }

        .table-data th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        .tally-box {
            min-height: 24px;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 6px 6px;
        }
    </style>
</head>

<body class="p-2 md:p-6">

    <!-- FLOATING ACTION TOOLBAR (NO PRINT) -->
    <div
        class="no-print sticky top-2 z-50 flex flex-wrap justify-center items-center py-2.5 px-4 bg-slate-900 text-white gap-2.5 rounded-2xl shadow-xl max-w-4xl mx-auto mb-6 border border-slate-700">
        <div
            class="px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-xl text-xs font-bold flex items-center gap-1.5">
            📋 FORMAT LEMBAR PENILAIAN DEWAN JURI
        </div>

        <div class="flex items-center gap-1.5">
            <!-- Tombol Cetak Semua (2 Lembar) -->
            <a href="{{ route('penilaian-ujian-alquran.cetak-format', array_filter(['tahun_id' => $ujian->tahun_pelajaran_id, 'ruangan_id' => $ruanganAktif?->id])) }}"
                class="px-3.5 py-1.5 {{ empty($filterJuri) ? 'bg-emerald-600 text-white font-black' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold' }} rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm">
                <i class="bi bi-printer-fill"></i>
                <span>Cetak 2 Lembar (Semua Juri)</span>
            </a>

            <!-- Tombol Hanya Juri 1 -->
            <a href="{{ route('penilaian-ujian-alquran.cetak-format', array_filter(['tahun_id' => $ujian->tahun_pelajaran_id, 'ruangan_id' => $ruanganAktif?->id, 'juri' => 'juri1'])) }}"
                class="px-3 py-1.5 {{ $filterJuri === 'juri1' ? 'bg-rose-600 text-white font-black' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold' }} rounded-xl text-xs flex items-center gap-1.5 transition-all">
                <span>Lembar Juri 1 (Jali)</span>
            </a>

            <!-- Tombol Hanya Juri 2 -->
            <a href="{{ route('penilaian-ujian-alquran.cetak-format', array_filter(['tahun_id' => $ujian->tahun_pelajaran_id, 'ruangan_id' => $ruanganAktif?->id, 'juri' => 'juri2'])) }}"
                class="px-3 py-1.5 {{ $filterJuri === 'juri2' ? 'bg-amber-600 text-white font-black' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold' }} rounded-xl text-xs flex items-center gap-1.5 transition-all">
                <span>Lembar Juri 2 (Khofi)</span>
            </a>
        </div>

        <button onclick="window.print()"
            class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs flex items-center gap-1 cursor-pointer transition-all active:scale-95 shadow-md">
            🖨️ Cetak Dokumen
        </button>

        <button onclick="window.close()"
            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    @php
        $sheetsToRender = [];
        if (empty($filterJuri) || $filterJuri === 'juri1') {
            $sheetsToRender[] = [
                'type' => 'juri1',
                'title_juri' => 'JURI 1 — BIDANG KHOTHO\' JALI',
                'sub_juri' => 'PENILAIAN KESALAHAN TAJWID BERAT / NYATA',
                'juri' => $juri1,
                'bobot' => (int) $ujian->bobot_jali,
                'warna_badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                'panduan' =>
                    'Mengamati dan menghitung kesalahan tajwid nyata/berat: perubahan huruf hijaiyyah (seperti ح jadi هـ, ع jadi ء, ص jadi س), harakat tertukar (dhammah/fathah/kasrah), penambahan/pengurangan huruf, atau ada kata/kalimat terlewat.',
                'label_col' => 'Khotho\' Jali',
            ];
        }
        if (empty($filterJuri) || $filterJuri === 'juri2') {
            $sheetsToRender[] = [
                'type' => 'juri2',
                'title_juri' => 'JURI 2 — BIDANG KHOTHO\' KHOFI',
                'sub_juri' => 'PENILAIAN KESALAHAN TAJWID RINGAN / SAMAR',
                'juri' => $juri2,
                'bobot' => (int) $ujian->bobot_khofi,
                'warna_badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                'panduan' =>
                    'Mengamati dan menghitung kesalahan tajwid samar/ringan: panjang kadar mad kurang/berlebih (2 s/d 6 harakat), ketidaksempurnaan ghunnah (ikhfa\', iqlab, idgham), kesalahan tebal/tipis (tafkhim/tarqiq), serta ketepatan pantulan qalqalah.',
                'label_col' => 'Khotho\' Khofi',
            ];
        }
    @endphp

    @foreach ($sheetsToRender as $sheetIndex => $sheet)
        <div class="sheet-container {{ !$loop->last ? 'page-break' : '' }}">

            <!-- 1. KOP SURAT RESMI -->
            <div class="kop-surat">
                <div class="kop-left">
                    <img src="{{ asset(getSetting('kop_logo', 'assets/LOGO MDT.png')) }}"
                        alt="Logo MDT Hidayatus Shibyan">
                </div>

            </div>

            <!-- 2. JUDUL LEMBAR PENILAIAN -->
            <div class="text-center my-2">
                <h3 class="text-xs font-black uppercase underline tracking-wider text-slate-950">
                    LEMBAR PENILAIAN UJIAN MEMBACA AL-QUR'AN
                </h3>
                <div
                    class="inline-block px-3 py-0.5 mt-1 border rounded-md font-extrabold text-[10px] uppercase tracking-wide {{ $sheet['warna_badge'] }}">
                    {{ $sheet['title_juri'] }}
                </div>
                <p class="text-[9px] font-bold text-slate-600 mt-0.5">
                    {{ $sheet['sub_juri'] }}
                </p>
            </div>

            <!-- 3. INFORMASI AGENDA & DEWAN JURI -->
            <table class="w-full text-[9.5px] mb-2 leading-tight bg-slate-50/80 p-2 border border-slate-200 rounded">
                <tr>
                    <td class="w-24 font-bold text-slate-700">Agenda Ujian</td>
                    <td class="w-2.5">:</td>
                    <td class="font-semibold">{{ $ujian->nama_ujian }}</td>
                    <td class="w-28 font-bold text-slate-700">Tahun Pelajaran</td>
                    <td class="w-2.5">:</td>
                    <td class="font-semibold">{{ $ujian->tahunPelajaran->nama_masehi ?? '-' }} M /
                        {{ $ujian->tahunPelajaran->nama_hijriyah ?? '-' }} H</td>
                </tr>
                <tr>
                    <td class="font-bold text-slate-700">Hari / Tanggal</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($ujian->tanggal_ujian ?? ($ujian->tanggal_pelaksanaan ?? now()))->translatedFormat('l, d F Y') }}
                    </td>
                    <td class="font-bold text-slate-700">Ruangan / Kelas</td>
                    <td>:</td>
                    <td><strong>{{ $ruanganAktif ? $ruanganAktif->nama_ruangan . ' (' . ($ruanganAktif->level->nama_level ?? '') . ')' : 'Semua Ruangan' }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="font-bold text-slate-700">Ustadz Penguji</td>
                    <td>:</td>
                    <td class="font-bold text-slate-900">
                        {{ $sheet['juri']?->ustadz?->nama_lengkap ?? '( Belum Ditetapkan )' }}
                        <span class="text-[8.5px] font-mono font-normal text-slate-500">
                            (NIGM: {{ $sheet['juri']?->ustadz?->nigm ?? '-' }})
                        </span>
                        @if ($sheet['juri']?->is_penanggung_jawab)
                            <span
                                class="text-[8px] font-black text-amber-700 bg-amber-100 border border-amber-300 px-1.5 py-0.2 rounded ml-1">
                                Penanggung Jawab
                            </span>
                        @endif
                    </td>
                    <td class="font-bold text-slate-700">Bobot Pengurangan</td>
                    <td>:</td>
                    <td class="font-bold text-rose-700">
                        -{{ $sheet['bobot'] }} Poin / Kesalahan
                    </td>
                </tr>
            </table>

            <!-- 4. PANDUAN RINGKAS PENILAIAN JURI -->
            <div
                class="p-2 mb-2 bg-amber-500/[0.06] border border-dashed border-amber-400/80 rounded text-[8.5px] text-slate-800 leading-tight">
                <strong>Ketentuan Penilaian {{ $sheet['title_juri'] }}:</strong>
                {{ $sheet['panduan'] }}
                <span class="block mt-0.5 text-slate-600 italic">
                    * Berikan tanda turus ( | ) pada kolom Coretan Kesalahan setiap kali murid berbuat salah, kemudian
                    hitung total kesalahan di kolom Total.
                </span>
            </div>

            <!-- 5. TABEL LEMBAR PENILAIAN PESERTA -->
            <table class="table-data">
                <thead>
                    <tr>
                        <th style="width: 24px;">No</th>
                        <th style="width: 70px;">No. Peserta</th>
                        <th style="width: 60px;">NISM</th>
                        <th>Nama Lengkap Murid</th>
                        <th style="width: 55px;">Ruangan</th>
                        <th style="width: 155px;">Coretan Hitungan / Turus Kesalahan<br><span
                                class="text-[7.5px] font-normal font-sans">(Tanda | Tiap Kesalahan)</span></th>
                        <th style="width: 45px;">Total<br><span class="text-[7.5px] font-normal font-sans">(Jml
                                Err)</span></th>
                        <th style="width: 52px;">Minus<br><span
                                class="text-[7.5px] font-normal font-sans">(-{{ $sheet['bobot'] }} pt)</span></th>
                        <th style="width: 110px;">Catatan Koreksi Tajwid</th>
                        <th style="width: 40px;">Paraf</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesertas as $idx => $p)
                        @php
                            $errCount = $sheet['type'] === 'juri1' ? $p->jumlah_khoto_jali : $p->jumlah_khoto_khofi;
                            $minusVal = $errCount * $sheet['bobot'];
                        @endphp
                        <tr class="h-7.5">
                            <td class="text-center font-mono text-[9px]">{{ $idx + 1 }}</td>
                            <td class="text-center font-mono font-bold text-[8.5px]">{{ $p->nomor_peserta ?? '-' }}
                            </td>
                            <td class="text-center font-mono text-[8.5px]">{{ $p->murid->nism ?? '-' }}</td>
                            <td class="font-bold uppercase text-[9.5px] leading-tight">
                                {{ $p->murid->nama_lengkap ?? '-' }}</td>
                            <td class="text-center text-[8.5px]">{{ $p->ruangan->nama_ruangan ?? '-' }}</td>

                            <!-- Area Kotak Tally Hitungan Turus -->
                            <td class="tally-box px-2 text-center text-xs font-mono font-bold text-slate-700">
                                @if ($errCount > 0)
                                    <span
                                        class="text-[9px] tracking-widest">{{ str_repeat('|', min($errCount, 15)) }}{{ $errCount > 15 ? '+' : '' }}</span>
                                @endif
                            </td>

                            <!-- Total Jumlah Kesalahan -->
                            <td
                                class="text-center font-bold font-mono text-[10px] {{ $sheet['type'] === 'juri1' ? 'text-rose-700' : 'text-amber-700' }}">
                                {{ $errCount > 0 ? $errCount : '' }}
                            </td>

                            <!-- Total Minus Poin -->
                            <td class="text-center font-black font-mono text-[10px] text-rose-800">
                                {{ $minusVal > 0 ? '-' . $minusVal : '' }}
                            </td>

                            <!-- Catatan Koreksi Tajwid -->
                            <td class="text-[8px] text-slate-600 leading-tight">
                                {{ $sheet['type'] === 'juri1' ? $p->catatan_juri ?? '' : '' }}
                            </td>

                            <!-- Paraf -->
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-6 text-slate-400 italic text-[10px]">
                                Belum ada peserta terdaftar pada agenda / ruangan ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- 6. TANDA TANGAN JURI & PENGASUH -->
            <table class="w-full text-center mt-3 text-[9px]" style="table-layout: fixed; page-break-inside: avoid;">
                <tr>
                    <td class="w-1/2 align-top">
                        <p class="mb-0 text-slate-700">Mengesahkan,</p>
                        <p class="font-bold text-slate-900">Pengasuh MDT Hidayatus Shibyan</p>
                        <div class="my-1 flex justify-center items-center" style="height: 50px;">
                            @if (!empty($pengasuh?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                ) !!}
                            @endif
                        </div>
                        <p class="font-bold underline uppercase text-slate-950">
                            {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                        </p>
                        <p class="text-[8px] text-slate-500">Pengasuh Madrasah</p>
                    </td>

                    <td class="w-1/2 align-top">
                        <p class="mb-0 text-slate-700">
                            Somorkoneng,
                            {{ \Carbon\Carbon::parse($ujian->tanggal_ujian ?? ($ujian->tanggal_pelaksanaan ?? now()))->translatedFormat('d F Y') }}
                        </p>
                        <p class="font-bold text-slate-900">
                            {{ $sheet['title_juri'] }}
                        </p>
                        <div class="my-1 flex justify-center items-center" style="height: 50px;">
                            @if (!empty($sheet['juri']?->ustadz_id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(48)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $sheet['juri']->ustadz_id]),
                                ) !!}
                            @endif
                        </div>
                        <p class="font-bold underline uppercase text-slate-950">
                            {{ $sheet['juri']?->ustadz?->nama_lengkap ?? '( ............................................................ )' }}
                        </p>
                        <p class="text-[8px] font-mono text-slate-600">
                            NIGM: {{ $sheet['juri']?->ustadz?->nigm ?? '-' }}
                            @if ($sheet['juri']?->is_penanggung_jawab)
                                • <strong>Penanggung Jawab Ujian</strong>
                            @endif
                        </p>
                    </td>
                </tr>
            </table>

        </div>
    @endforeach

    @if (request()->has('print') || request()->has('auto_print') || request()->has('download'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 500);
            });
        </script>
    @endif

</body>

</html>
