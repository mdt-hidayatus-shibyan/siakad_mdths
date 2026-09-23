<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Bintang Pelajar - {{ $ujianTerpilih->nama_ujian ?? 'Ujian' }}</title>
    <!-- Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 1cm;
                size: A4 portrait;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        body {
            font-family: "Plus Jakarta Sans", Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
            zoom: 92%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 30%;
        }

        .kop-logo {
            max-width: 100%;
            height: auto;
            max-height: 90px;
            display: inline-block;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.4;
            width: 70%;
        }

        .kop-right h1 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-right p {
            margin: 2px 0;
            font-size: 11px;
            color: #475569;
        }

        /* Section Heading */
        .section-header {
            background: #f1f5f9;
            padding: 6px 10px;
            font-weight: 800;
            font-size: 12px;
            color: #0f172a;
            border-left: 4px solid #2563eb;
            margin: 15px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sub-header {
            font-size: 11px;
            font-weight: 800;
            color: #334155;
            margin: 10px 0 4px 0;
            padding-left: 4px;
            text-transform: uppercase;
        }

        /* Tabel Data */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5px;
        }

        .data-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
            text-align: center;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }

        .data-table td.text-left {
            text-align: left;
        }

        .data-table td.text-right {
            text-align: right;
        }

        .w-rank {
            width: 45px;
            font-weight: 800;
        }

        .w-nism {
            width: 80px;
            font-family: monospace;
        }

        .w-lp {
            width: 35px;
        }

        .w-nilai {
            width: 75px;
            font-weight: 800;
        }

        .rank-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 10px;
            text-align: center;
        }

        .rank-1 {
            background-color: #f59e0b;
            color: #fff;
        }

        .rank-2 {
            background-color: #94a3b8;
            color: #fff;
        }

        .rank-3 {
            background-color: #b45309;
            color: #fff;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Tombol Aksi (Sembunyi saat diprint) -->
    <div class="no-print"
        style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="display: flex; gap: 8px; align-items: center;">
            <button onclick="window.print()"
                style="padding: 7px 14px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 12px;">
                Cetak Dokumen
            </button>
            <button onclick="window.close()"
                style="padding: 7px 14px; background: #64748b; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 12px;">
                Tutup Tab
            </button>
        </div>

        <div style="display: flex; gap: 6px; align-items: center; font-size: 11px; font-weight: bold;">
            <span>Tampilkan:</span>
            <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => $ujianTerpilih->id, 'kategori' => 'semua']) }}"
                style="padding: 4px 8px; border-radius: 4px; text-decoration: none; {{ $kategori == 'semua' ? 'background: #2563eb; color: #fff;' : 'background: #e2e8f0; color: #334155;' }}">
                Semua
            </a>
            <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => $ujianTerpilih->id, 'kategori' => 'level']) }}"
                style="padding: 4px 8px; border-radius: 4px; text-decoration: none; {{ $kategori == 'level' ? 'background: #2563eb; color: #fff;' : 'background: #e2e8f0; color: #334155;' }}">
                Bintang Tingkat
            </a>
            <a href="{{ route('bintang-pelajar.cetak', ['ujian_id' => $ujianTerpilih->id, 'kategori' => 'ruangan']) }}"
                style="padding: 4px 8px; border-radius: 4px; text-decoration: none; {{ $kategori == 'ruangan' ? 'background: #2563eb; color: #fff;' : 'background: #e2e8f0; color: #334155;' }}">
                Bintang Ruangan
            </a>
        </div>
    </div>

    @php
        $pengasuh =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala');
    @endphp

    <!-- KOP SURAT BERLOGO -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>DAFTAR PEMENANG BINTANG PELAJAR</h1>
            <p>Tahun Pelajaran: <strong>{{ $ujianTerpilih->tahunPelajaran->nama_hijriyah ?? '-' }} H |
                    {{ $ujianTerpilih->tahunPelajaran->nama_masehi ?? '-' }} M</strong></p>
            <p>Agenda Ujian: <strong>{{ strtoupper($ujianTerpilih->nama_ujian) }}</strong></p>
        </div>
    </div>

    <!-- BAGIAN 1: BINTANG TINGKAT (PER JENJANG) -->
    @if ($kategori === 'semua' || $kategori === 'level')
        <div class="section-header">
            I. BINTANG TINGKAT (TOP 3 PER JENJANG / LEVEL)
        </div>

        @forelse ($bintangLevel as $namaLevel => $murids)
            <div class="sub-header">
                Jenjang: {{ $namaLevel }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-rank">Rank</th>
                        <th class="w-nism">NISM</th>
                        <th style="text-align: left; padding-left: 8px;">Nama Murid</th>
                        <th class="w-lp">L/P</th>
                        <th style="width: 80px;">Kelas</th>
                        <th style="text-align: left; padding-left: 8px;">Nama Ayah</th>
                        <th style="text-align: left; padding-left: 8px;">Kampung</th>
                        <th class="w-nilai">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($murids as $i => $s)
                        @php
                            $namaAyah = $s->murid->nama_ayah ?? ($s->murid->waliMurid->nama_kepala_keluarga ?? '-');
                            $kampung =
                                $s->murid->waliMurid->kampung->nama_kampung ??
                                ($s->murid->waliMurid->alamat_detail ?? '-');
                        @endphp
                        <tr style="{{ $i == 0 ? 'background-color: #fefce8;' : '' }}">
                            <td>
                                <span class="rank-badge {{ $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : 'rank-3') }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="w-nism">{{ $s->murid->nism ?? '-' }}</td>
                            <td class="text-left" style="padding-left: 8px; font-weight: bold;">
                                {{ $s->murid->nama_lengkap }}
                            </td>
                            <td>{{ $s->murid->jenis_kelamin }}</td>
                            <td>{{ $s->ruangan_nama }}</td>
                            <td class="text-left" style="padding-left: 8px;">{{ $namaAyah }}</td>
                            <td class="text-left" style="padding-left: 8px;">{{ $kampung }}</td>
                            <td class="text-right" style="padding-right: 8px; font-weight: 800;">
                                {{ number_format($s->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align: center; color: #64748b; padding: 15px;">Belum ada data bintang tingkat yang tersedia.
            </p>
        @endforelse
    @endif

    <!-- BAGIAN 2: BINTANG RUANGAN (PER KELAS) -->
    @if ($kategori === 'semua' || $kategori === 'ruangan')
        @if ($kategori === 'semua')
            <div style="margin-top: 20px;"></div>
        @endif

        <div class="section-header">
            {{ $kategori === 'semua' ? 'II.' : 'I.' }} BINTANG RUANGAN (TOP 3 PER RUANGAN / KELAS)
        </div>

        @forelse ($bintangRuangan as $namaRuangan => $murids)
            <div class="sub-header">
                Ruangan Kelas: {{ $namaRuangan }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-rank">Rank</th>
                        <th class="w-nism">NISM</th>
                        <th style="text-align: left; padding-left: 8px;">Nama Murid</th>
                        <th class="w-lp">L/P</th>
                        <th style="text-align: left; padding-left: 8px;">Nama Ayah</th>
                        <th style="text-align: left; padding-left: 8px;">Kampung</th>
                        <th class="w-nilai">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($murids as $i => $s)
                        @php
                            $namaAyah = $s->murid->nama_ayah ?? ($s->murid->waliMurid->nama_kepala_keluarga ?? '-');
                            $kampung =
                                $s->murid->waliMurid->kampung->nama_kampung ??
                                ($s->murid->waliMurid->alamat_detail ?? '-');
                        @endphp
                        <tr style="{{ $i == 0 ? 'background-color: #fefce8;' : '' }}">
                            <td>
                                <span class="rank-badge {{ $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : 'rank-3') }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="w-nism">{{ $s->murid->nism ?? '-' }}</td>
                            <td class="text-left" style="padding-left: 8px; font-weight: bold;">
                                {{ $s->murid->nama_lengkap }}
                            </td>
                            <td>{{ $s->murid->jenis_kelamin }}</td>
                            <td class="text-left" style="padding-left: 8px;">{{ $namaAyah }}</td>
                            <td class="text-left" style="padding-left: 8px;">{{ $kampung }}</td>
                            <td class="text-right" style="padding-right: 8px; font-weight: 800;">
                                {{ number_format($s->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align: center; color: #64748b; padding: 15px;">Belum ada data bintang ruangan yang
                tersedia.</p>
        @endforelse
    @endif

    <!-- AREA TANDA TANGAN -->
    <table style="width: 100%; border: none; margin-top: 25px; page-break-inside: avoid;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 11px;">Mengetahui,</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Pengasuh</p>
                <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                    @if (!empty($pengasuh?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                        ) !!}
                    @else
                        <div style="height: 50px;"></div>
                    @endif
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'Nama Kepala Belum Diatur') }}
                </p>
            </td>

            <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                <p style="margin: 0; font-size: 11px;">Somor Koneng,
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 6px 0; font-size: 11px; font-weight: bold;">Panitia Ujian / Sie. Kurikulum</p>
                <div style="min-height: 60px; display: flex; justify-content: center; align-items: center;">
                    <div style="height: 50px;"></div>
                </div>
                <p
                    style="margin: 6px 0 0 0; font-size: 11px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    ( ............................................ )
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
