<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <title>Cetak Data Wali Murid Aktif - {{ $filterKampung ? $filterKampung->nama_kampung : 'Semua Kampung' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        @media print {
            body {
                padding: 0;
                zoom: 88%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            tr {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 10.5px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
            border-radius: 8px;
        }

        .btn-print {
            padding: 6px 14px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-close {
            padding: 6px 14px;
            background: #e2e8f0;
            color: #334155;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 90px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.35;
        }

        .kop-right h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .kop-right p {
            margin: 2px 0 0 0;
            font-size: 10.5px;
            color: #334155;
        }

        .summary-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 10px;
            font-weight: 600;
            color: #334155;
        }

        .group-header {
            background: #0f172a;
            color: #ffffff;
            padding: 6px 10px;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-radius: 4px 4px 0 0;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .group-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .data-table th {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: 800;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .anak-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .anak-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .anak-item:last-child {
            border-bottom: none;
        }

        .anak-nama {
            font-weight: 700;
            color: #0f172a;
        }

        .anak-meta {
            color: #64748b;
            font-size: 9px;
            margin-left: 6px;
        }

        .ruangan-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 800;
            font-size: 8.5px;
            border: 1px solid #bae6fd;
            white-space: nowrap;
        }

        .signature-table {
            width: 100%;
            border: none;
            margin-top: 25px;
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

<body onload="setTimeout(function(){ window.print(); }, 800)">

    <!-- Action Bar (No Print) -->
    <div class="no-print no-print-bar">
        <div>
            <strong>Cetak Data Wali Murid Aktif</strong> &mdash; MDT Hidayatus Shibyan
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M6 9V2h12v7"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Sekarang
            </button>
            <button onclick="window.close()" class="btn-close">Tutup</button>
        </div>
    </div>

    @php
        $pengasuh = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh');
        $kepalaMadrasah =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Sekretaris');

        $totalWaliSemua = 0;
        $totalMuridSemua = 0;
        foreach ($groupedWalis as $group) {
            $totalWaliSemua += $group->count();
            foreach ($group as $w) {
                $totalMuridSemua += $w->murids->count();
            }
        }
    @endphp

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>DATA WALI MURID AKTIF PER KAMPUNG / ZONASI</h1>
            <p>Tahun Pelajaran: <strong>{{ $tahunAktif->nama_hijriyah ?? '-' }} H |
                    {{ $tahunAktif->nama_masehi ?? '-' }} M</strong></p>
            <p>Wilayah:
                <strong>{{ $filterKampung ? "Dusun/Kampung ({$filterKampung->kode}) {$filterKampung->nama_kampung}" : 'Semua Kampung / Zonasi' }}</strong>
            </p>
            <p>Dicetak Pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>
    </div>

    <!-- SUMMARY BAR -->
    <div class="summary-bar">
        <div>
            <span>Total Kelompok Wilayah: <strong>{{ $groupedWalis->count() }} Kampung</strong></span>
        </div>
        <div>
            <span>Total Kepala Keluarga (Wali Aktif): <strong>{{ $totalWaliSemua }} KK</strong></span>
            <span style="margin: 0 8px;">|</span>
            <span>Total Murid Aktif: <strong>{{ $totalMuridSemua }} Anak</strong></span>
        </div>
    </div>

    @php $globalNo = 1; @endphp

    @forelse($groupedWalis as $namaGroup => $walis)
        @php
            $totalAnakGroup = $walis->sum(function ($w) {
                return $w->murids->count();
            });
        @endphp

        <div class="group-header">
            <span><i style="font-style: normal;">&#9654;</i> KAMPUNG: {{ strtoupper($namaGroup) }}</span>
            <span class="group-badge">{{ $walis->count() }} KK &bull; {{ $totalAnakGroup }} Murid</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 75px; text-align: center;">No. Reg</th>
                    <th style="width: 110px; text-align: center;">No. KK</th>
                    <th style="width: 160px;">Kepala Keluarga (Wali)</th>
                    <th style="width: 95px; text-align: center;">No. HP / WA</th>
                    <th style="width: 140px;">Alamat Detail</th>
                    <th>Nama Putra / Putri &amp; Ruangan Kelas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($walis as $wali)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $globalNo++ }}</td>
                        <td style="text-align: center; font-family: monospace; font-weight: 700;">
                            {{ $wali->no_registrasi ?? '-' }}</td>
                        <td style="text-align: center; font-family: monospace;">{{ $wali->no_kk ?? '-' }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $wali->nama_kepala_keluarga }}</strong>
                            <div style="font-size: 8.5px; color: #64748b;">
                                Status: {{ $wali->kepala_keluarga ?? 'Ayah' }}
                                @if ($wali->is_asatidz || $wali->is_ustadz)
                                    <span style="color: #059669; font-weight: 700;">(Asatidz)</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">{{ $wali->no_hp ?: '-' }}</td>
                        <td>{{ $wali->alamat_detail ?: '-' }}</td>
                        <td>
                            @if ($wali->murids->isNotEmpty())
                                <ul class="anak-list">
                                    @foreach ($wali->murids as $anak)
                                        <li class="anak-item">
                                            <div>
                                                <span class="anak-nama">{{ $anak->nama_lengkap }}</span>
                                                <span class="anak-meta">({{ $anak->jenis_kelamin }} &bull; NISM:
                                                    {{ $anak->nism }}{{ $anak->nik ? ' &bull; NIK: ' . $anak->nik : '' }})</span>
                                            </div>
                                            <div>
                                                <span class="ruangan-badge">
                                                    {{ $anak->nama_ruangan_aktif }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color: #94a3b8; font-style: italic; font-size: 9px;">Tidak ada murid
                                    aktif</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div style="text-align: center; padding: 30px; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b;">
            Tidak ada data wali murid aktif yang ditemukan.
        </div>
    @endforelse

    <!-- AREA TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p style="margin: 0; font-size: 10.5px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Pengasuh Madrasah</p>
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 4px 0 0 0; font-size: 10.5px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Pengasuh Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%;">
                <p style="margin: 0; font-size: 10.5px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Sekretaris Madrasah</p>
                <div style="min-height: 55px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($kepalaMadrasah?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $kepalaMadrasah->id]),
                        ) !!}
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 4px 0 0 0; font-size: 10.5px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $kepalaMadrasah?->anggota?->nama_lengkap ?? ($kepalaMadrasah?->nama ?? 'Nama Sekretaris Belum Diatur') }}
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
