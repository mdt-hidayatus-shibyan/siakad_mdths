<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ $mode === 'leger' ? 'Blangko Leger Penilaian Ujian' : 'Daftar Penilaian Ujian' }} -
        {{ $ruangan->nama_ruangan }}
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 {{ $mode === 'leger' ? 'landscape' : 'portrait' }};
                margin: 0.9cm;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sheet {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                min-height: auto !important;
            }

            .page-break {
                page-break-after: always;
                break-after: page;
            }

            .data-table tr {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            background: #fff;
            max-width: {{ $mode === 'leger' ? '29.7cm' : '21cm' }};
            min-height: {{ $mode === 'leger' ? '21cm' : '29.7cm' }};
            margin: 16px auto 26px auto;
            padding: 0.9cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 3px;
            gap: 14px;
        }

        .garis-ganda {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 12px;
        }

        .kop-logo {
            max-width: 100%;
            max-height: 82px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .kop-right {
            text-align: right;
            line-height: 1.38;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .kop-right p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        .info-grid td {
            padding: 4px 8px;
            vertical-align: middle;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: {{ $mode === 'leger' ? '9.5px' : '10.5px' }};
        }

        .data-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            text-align: center;
            padding: {{ $mode === 'leger' ? '6px 4px' : '7px 6px' }};
            border: 1px solid #475569;
            font-size: {{ $mode === 'leger' ? '8.5px' : '9.5px' }};
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .data-table td {
            padding: {{ $mode === 'leger' ? '5.5px 5px' : '6.5px 7px' }};
            border: 1px solid #475569;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .signature-table {
            width: 100%;
            border: none;
            margin-top: 22px;
            page-break-inside: avoid;
        }

        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <!-- TOOLBAR PENGATURAN CETAK (NO PRINT) -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-2.5 px-4 bg-slate-900 text-white gap-2.5 border-b border-slate-700 shadow-lg">
        <div
            class="px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold">
            📝 FORM PENILAIAN UJIAN — {{ strtoupper($ruangan->nama_ruangan) }}
        </div>

        <!-- Mode Switcher -->
        <div class="flex items-center gap-1 bg-slate-800 p-1 rounded-lg border border-slate-700">
            <a href="{{ route('rombongan-belajar.print-penilaian', array_filter(['id' => $ruangan->id, 'mode' => 'mapel', 'ujian_id' => $selectedUjianId, 'mapel_id' => $selectedMapelId, 'semua_status' => $semuaStatus ? 1 : null])) }}"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-all {{ $mode === 'mapel' ? 'bg-amber-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                Per Mapel (Portrait)
            </a>
            <a href="{{ route('rombongan-belajar.print-penilaian', array_filter(['id' => $ruangan->id, 'mode' => 'leger', 'ujian_id' => $selectedUjianId, 'semua_status' => $semuaStatus ? 1 : null])) }}"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-all {{ $mode === 'leger' ? 'bg-purple-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                Blangko Leger (Landscape)
            </a>
        </div>

        <!-- Form Pilihan Ujian & Mapel -->
        <form action="{{ route('rombongan-belajar.print-penilaian', $ruangan->id) }}" method="GET"
            class="flex flex-wrap items-center gap-2 m-0">
            <input type="hidden" name="mode" value="{{ $mode }}">

            @if ($daftarUjian->isNotEmpty())
                <select name="ujian_id" onchange="this.form.submit()"
                    class="h-8 px-2.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-semibold text-white cursor-pointer">
                    <option value="">Agenda Ujian: Tulis Manual</option>
                    @foreach ($daftarUjian as $uj)
                        <option value="{{ $uj->id }}" {{ (string) $selectedUjianId === (string) $uj->id ? 'selected' : '' }}>
                            {{ $uj->nama_ujian }}
                        </option>
                    @endforeach
                </select>
            @endif

            @if ($mode === 'mapel' && $mataPelajarans->isNotEmpty())
                <select name="mapel_id" onchange="this.form.submit()"
                    class="h-8 px-2.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-semibold text-white cursor-pointer">
                    <option value="">Mapel: Kosong (Tulis Manual)</option>
                    <option value="semua" {{ $selectedMapelId === 'semua' ? 'selected' : '' }}>
                        Semua Mapel ({{ $mataPelajarans->count() }} Lembar)
                    </option>
                    @foreach ($mataPelajarans as $mp)
                        <option value="{{ $mp->id }}" {{ (string) $selectedMapelId === (string) $mp->id ? 'selected' : '' }}>
                            {{ $mp->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            @endif

            <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-300 cursor-pointer ml-1">
                <input type="checkbox" name="semua_status" value="1" onchange="this.form.submit()"
                    {{ $semuaStatus ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800">
                <span>Sertakan Non-Aktif</span>
            </label>
        </form>

        <button onclick="window.print()"
            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    @php
        $waliUstadz = $ruangan->waliRuangan ?? null;
        $totalL = $ruangan->murids->where('jenis_kelamin', 'L')->count();
        $totalP = $ruangan->murids->where('jenis_kelamin', 'P')->count();
    @endphp

    @if ($mode === 'mapel')
        <!-- ========================================================== -->
        <!-- MODE 1: FORM PENILAIAN UJIAN PER MATA PELAJARAN (PORTRAIT) -->
        <!-- ========================================================== -->
        @foreach ($sheets as $sheet)
            <div class="sheet {{ !$loop->last ? 'page-break' : '' }}">
                <!-- KOP SURAT -->
                <div class="kop-surat">
                    <div style="width: 56%;">
                        <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
                    </div>
                    <div class="kop-right">
                        <h1>DAFTAR PENILAIAN UJIAN</h1>
                        <p>Tahun Pelajaran:
                            <strong>{{ $ruangan->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                                {{ $ruangan->tahunPelajaran->nama_masehi ?? '-' }} M</strong>
                        </p>
                        <p>Kelas / Ruang: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong>
                            ({{ $ruangan->level->nama_level ?? '-' }})</p>
                        <p>Peserta: <strong>{{ $ruangan->murids->count() }} Murid</strong> (L: {{ $totalL }}, P:
                            {{ $totalP }})</p>
                    </div>
                </div>
                <div class="garis-ganda"></div>

                <!-- INFORMASI UJIAN & MATA PELAJARAN -->
                <table class="info-grid">
                    <tr>
                        <td style="width: 17%; font-weight: 700;">Agenda Ujian</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 34%; font-weight: 800; text-transform: uppercase;">
                            {{ $ujianTerpilih?->nama_ujian ?? '................................................' }}
                        </td>
                        <td style="width: 17%; font-weight: 700;">Hari / Tanggal</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 28%;">............................................</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Mata Pelajaran</td>
                        <td>:</td>
                        <td style="font-weight: 800; text-transform: uppercase;">
                            {{ $sheet->mapel_nama ?? '................................................' }}
                        </td>
                        <td style="font-weight: 700;">Wali Ruangan</td>
                        <td>:</td>
                        <td style="font-weight: 700;">{{ $waliUstadz?->nama_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Penguji / Korektor</td>
                        <td>:</td>
                        <td style="font-weight: 700;">
                            {{ $sheet->pengampu_nama ?? '................................................' }}
                        </td>
                        <td style="font-weight: 700;">KKM / Skala</td>
                        <td>:</td>
                        <td>60 / 0 - 100</td>
                    </tr>
                </table>

                <!-- TABEL PENILAIAN MURID -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 28px;">No</th>
                            <th style="width: 72px;">NISM</th>
                            <th style="text-align: left; padding-left: 8px;">Nama Lengkap Murid</th>
                            <th style="width: 30px;">L/P</th>
                            <th style="width: 68px;">Nilai<br><span style="font-size: 7.5px; font-weight: 500;">(Angka)</span></th>
                            <th style="width: 68px;">Nilai<br><span style="font-size: 7.5px; font-weight: 500;">(Huruf/Pred)</span></th>
                            <th style="width: 120px;">Keterangan</th>
                            <th style="width: 48px;">Paraf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ruangan->murids as $murid)
                            <tr style="height: 25px; {{ $murid->status !== 'Aktif' ? 'color: #dc2626;' : '' }}">
                                <td style="text-align: center;">{{ $loop->iteration }}</td>
                                <td style="text-align: center; font-family: monospace;">{{ $murid->nism ?? '-' }}</td>
                                <td style="font-weight: 700; text-transform: uppercase;">
                                    {{ $murid->nama_lengkap }}
                                    @if ($murid->status !== 'Aktif')
                                        <span style="font-size: 8px; font-weight: 600;">({{ $murid->status }})</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $murid->jenis_kelamin }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 18px; color: #64748b;">
                                    Belum ada anggota aktif di ruangan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- CATATAN -->
                <div style="font-size: 9px; color: #475569; margin-top: -8px; font-style: italic;">
                    * Mohon lembar penilaian ini diserahkan kepada Panitia Ujian / Administrator setelah proses koreksi selesai.
                </div>

                <!-- TANDA TANGAN -->
                <table class="signature-table">
                    <tr>
                        <td style="width: 50%;">
                            <p style="margin: 0; font-size: 10px;">Mengetahui,</p>
                            <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Wali Ruangan
                                {{ strtoupper($ruangan->nama_ruangan) }}</p>
                            <div style="min-height: 58px; display: flex; justify-content: center; align-items: center;">
                                @if (!empty($waliUstadz?->id))
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(56)->generate(
                                        URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $waliUstadz->id]),
                                    ) !!}
                                @else
                                    <div style="height: 48px;"></div>
                                @endif
                            </div>
                            <p
                                style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                                {{ $waliUstadz?->nama_lengkap ?? '( ........................................... )' }}
                            </p>
                        </td>

                        <td style="width: 50%;">
                            <p style="margin: 0; font-size: 10px;">Somor Koneng, ..............................
                                {{ date('Y') }}</p>
                            <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Ustadz Penguji / Korektor</p>
                            <div style="min-height: 58px; display: flex; justify-content: center; align-items: center;">
                                @if (!empty($sheet->pengampu_id))
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(56)->generate(
                                        URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $sheet->pengampu_id]),
                                    ) !!}
                                @else
                                    <div style="height: 48px;"></div>
                                @endif
                            </div>
                            <p
                                style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                                {{ $sheet->pengampu_nama ?? '( ........................................... )' }}
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    @else
        <!-- ========================================================== -->
        <!-- MODE 2: BLANGKO LEGER PENILAIAN SEMUA MAPEL (LANDSCAPE)    -->
        <!-- ========================================================== -->
        <div class="sheet">
            <div class="kop-surat">
                <div style="width: 45%;">
                    <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
                </div>
                <div class="kop-right">
                    <h1>BLANGKO LEGER PENILAIAN UJIAN</h1>
                    <p>Tahun Pelajaran:
                        <strong>{{ $ruangan->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                            {{ $ruangan->tahunPelajaran->nama_masehi ?? '-' }} M</strong>
                    </p>
                    <p>Agenda Ujian:
                        <strong>{{ $ujianTerpilih?->nama_ujian ?? '........................................' }}</strong>
                        | Kelas/Ruang: <strong>{{ strtoupper($ruangan->nama_ruangan) }}</strong>
                        ({{ $ruangan->level->nama_level ?? '-' }})
                    </p>
                    <p>Wali Ruangan: <strong>{{ $waliUstadz?->nama_lengkap ?? '-' }}</strong> | Peserta:
                        <strong>{{ $ruangan->murids->count() }} Murid</strong> (L: {{ $totalL }}, P:
                        {{ $totalP }})</p>
                </div>
            </div>
            <div class="garis-ganda"></div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 24px;">No</th>
                        <th style="width: 64px;">NISM</th>
                        <th style="text-align: left; padding-left: 6px; min-width: 155px;">Nama Lengkap Murid</th>
                        <th style="width: 24px;">L/P</th>
                        @forelse ($mataPelajarans as $mp)
                            <th style="min-width: 44px; font-size: 8px;" title="{{ $mp->nama_mapel }}">
                                {{ strlen($mp->nama_mapel) > 12 ? substr($mp->nama_mapel, 0, 10) . '..' : $mp->nama_mapel }}
                            </th>
                        @empty
                            @for ($i = 1; $i <= 6; $i++)
                                <th style="min-width: 46px;">Mapel {{ $i }}</th>
                            @endfor
                        @endforelse
                        <th style="width: 46px;">Jumlah</th>
                        <th style="width: 46px;">Rata²</th>
                        <th style="width: 36px;">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @php $colCount = $mataPelajarans->isNotEmpty() ? $mataPelajarans->count() : 6; @endphp
                    @forelse ($ruangan->murids as $murid)
                        <tr style="height: 23px; {{ $murid->status !== 'Aktif' ? 'color: #dc2626;' : '' }}">
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $murid->nism ?? '-' }}</td>
                            <td style="font-weight: 700; text-transform: uppercase;">
                                {{ $murid->nama_lengkap }}
                                @if ($murid->status !== 'Aktif')
                                    <span style="font-size: 8px;">({{ $murid->status }})</span>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $murid->jenis_kelamin }}</td>
                            @for ($c = 0; $c < $colCount; $c++)
                                <td></td>
                            @endfor
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colCount + 7 }}" style="text-align: center; padding: 18px; color: #64748b;">
                                Belum ada anggota aktif di ruangan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- TANDA TANGAN -->
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;">
                        <p style="margin: 0; font-size: 10px;">Mengetahui,</p>
                        <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Pengasuh Madrasah</p>
                        <div style="min-height: 56px; display: flex; justify-content: center; align-items: center;">
                            @if (!empty($pengasuh?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(54)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                ) !!}
                            @else
                                <div style="height: 46px;"></div>
                            @endif
                        </div>
                        <p
                            style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                            {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Pengasuh Madrasah') }}
                        </p>
                    </td>
                    <td style="width: 50%;">
                        <p style="margin: 0; font-size: 10px;">Somor Koneng,
                            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p style="margin: 2px 0 6px 0; font-size: 10px; font-weight: bold;">Wali Ruangan
                            {{ strtoupper($ruangan->nama_ruangan) }}</p>
                        <div style="min-height: 56px; display: flex; justify-content: center; align-items: center;">
                            @if (!empty($waliUstadz?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(54)->generate(
                                    URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $waliUstadz->id]),
                                ) !!}
                            @else
                                <div style="height: 46px;"></div>
                            @endif
                        </div>
                        <p
                            style="margin: 5px 0 0 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                            {{ $waliUstadz?->nama_lengkap ?? '( ........................................... )' }}
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</body>

</html>
