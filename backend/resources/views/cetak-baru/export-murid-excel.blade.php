<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        .num-text {
            mso-number-format: "\@";
        }

        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }

        .header-sub {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
        }

        .header-meta {
            font-size: 9pt;
            text-align: center;
            color: #555555;
        }

        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #94a3b8;
            padding: 8px;
            text-align: center;
            font-size: 10pt;
        }

        td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9.5pt;
            vertical-align: middle;
        }

        .total-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 10pt;
            border-top: 2px solid #0f172a;
        }

        .stat-card td {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 9pt;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <td colspan="28" class="header-title">DATA INDUK SANTRI / MURID</td>
        </tr>
        <tr>
            <td colspan="28" class="header-sub">MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</td>
        </tr>
        <tr>
            <td colspan="28" class="header-meta">
                Tahun Pelajaran Aktif: {{ $tahunAktif->nama_hijriyah ?? '-' }} H | {{ $tahunAktif->nama_masehi ?? '-' }} M
                &bull;
                Filter Status: <b>{{ $status ?: 'Semua' }}</b>
                @if (!empty($search))
                    &bull; Kata Kunci: <b>"{{ $search }}"</b>
                @endif
                &bull;
                Total Data: <b>{{ $murids->count() }} Santri</b>
                &bull;
                Diexport Pada: {{ date('d-m-Y H:i:s') }} WIB
            </td>
        </tr>
        <tr>
            <td colspan="28"></td>
        </tr>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>NISM</th>
                <th>NISN</th>
                <th>NIK Santri</th>
                <th>Nama Lengkap</th>
                <th>Nama Panggilan</th>
                <th>L/P</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Umur</th>
                <th>Anak Ke</th>
                <th>Hub. Keluarga</th>
                <th>Status Santri</th>
                <th>Kelas / Rombel Aktif</th>
                <th>Jenjang / Level</th>
                <th>Tahun Masuk</th>
                <th>No. Registrasi KK</th>
                <th>No. KK</th>
                <th>Nama Kepala Keluarga (Wali)</th>
                <th>No. HP / WA Wali</th>
                <th>Kampung / Domisili</th>
                <th>Alamat Lengkap</th>
                <th>NIK Ayah</th>
                <th>Nama Ayah Kandung</th>
                <th>Status Ayah</th>
                <th>NIK Ibu</th>
                <th>Nama Ibu Kandung</th>
                <th>Status Ibu</th>
                <th>Kategori Khusus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $countL = 0;
                $countP = 0;
                $countYatim = 0;
                $countPiatu = 0;
                $countYatimPiatu = 0;
            @endphp

            @forelse($murids as $index => $m)
                @php
                    if ($m->jenis_kelamin === 'L') {
                        $countL++;
                    } else {
                        $countP++;
                    }

                    $umur = 0;
                    if ($m->tanggal_lahir) {
                        $umur = \Carbon\Carbon::parse($m->tanggal_lahir)->age;
                    }

                    $isYatim = $m->status_ayah === 'Meninggal' && $umur < 15;
                    $isPiatu = $m->status_ibu === 'Meninggal' && $umur < 15;

                    $kategoriKhusus = '-';
                    if ($isYatim && $isPiatu) {
                        $kategoriKhusus = 'Yatim Piatu';
                        $countYatimPiatu++;
                    } elseif ($isYatim) {
                        $kategoriKhusus = 'Yatim';
                        $countYatim++;
                    } elseif ($isPiatu) {
                        $kategoriKhusus = 'Piatu';
                        $countPiatu++;
                    }

                    $ruanganAktif = $m->ruangans->first()?->nama_ruangan ?? ($m->ruanganMasuk?->nama_ruangan ?? '-');
                    $levelAktif = $m->ruangans->first()?->level?->nama_level ?? ($m->levelMasuk?->nama_level ?? '-');
                    $wali = $m->waliMurid;
                @endphp
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td class="num-text" align="center">{{ $m->nism }}</td>
                    <td class="num-text" align="center">{{ $m->nisn ?: '-' }}</td>
                    <td class="num-text" align="center">{{ $m->nik ?: '-' }}</td>
                    <td><b>{{ $m->nama_lengkap }}</b></td>
                    <td>{{ $m->nama_panggilan ?: '-' }}</td>
                    <td align="center">{{ $m->jenis_kelamin }}</td>
                    <td>{{ $m->tempat_lahir ?: '-' }}</td>
                    <td align="center">{{ $m->tanggal_lahir ? \Carbon\Carbon::parse($m->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                    <td align="center">{{ $umur > 0 ? $umur . ' Thn' : '-' }}</td>
                    <td align="center">{{ $m->anak_ke ?: '-' }}</td>
                    <td align="center">{{ $m->hub_kel ?: 'Anak Kandung' }}</td>
                    <td align="center"><b>{{ $m->status }}</b></td>
                    <td align="center"><b>{{ $ruanganAktif }}</b></td>
                    <td align="center">{{ $levelAktif }}</td>
                    <td align="center">{{ $m->tahunMasuk?->nama_hijriyah ?? ($m->tahunMasuk?->nama_masehi ?? '-') }}</td>
                    <td class="num-text" align="center">{{ $wali?->no_registrasi ?: '-' }}</td>
                    <td class="num-text" align="center">{{ $wali?->no_kk ?: '-' }}</td>
                    <td>{{ $wali?->nama_kepala_keluarga ?: '-' }}</td>
                    <td class="num-text" align="center">{{ $wali?->no_hp ?: '-' }}</td>
                    <td>{{ $wali?->kampung ? "({$wali->kampung->kode}) {$wali->kampung->nama_kampung}" : '-' }}</td>
                    <td>{{ $wali?->alamat_detail ?: '-' }}</td>
                    <td class="num-text" align="center">{{ $m->nik_ayah ?: '-' }}</td>
                    <td>{{ $m->nama_ayah ?: '-' }}</td>
                    <td align="center">{{ $m->status_ayah ?: 'Hidup' }}</td>
                    <td class="num-text" align="center">{{ $m->nik_ibu ?: '-' }}</td>
                    <td>{{ $m->nama_ibu ?: '-' }}</td>
                    <td align="center">{{ $m->status_ibu ?: 'Hidup' }}</td>
                    <td align="center"><b>{{ $kategoriKhusus }}</b></td>
                </tr>
            @empty
                <tr>
                    <td colspan="29" align="center" style="padding: 20px; color: #64748b;">
                        Tidak ada data santri yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($murids->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" align="right"><b>TOTAL SANTRI:</b></td>
                    <td align="center"><b>{{ $murids->count() }}</b></td>
                    <td colspan="22">
                        <b>Laki-laki (L):</b> {{ $countL }} Santri &nbsp;|&nbsp;
                        <b>Perempuan (P):</b> {{ $countP }} Santri &nbsp;|&nbsp;
                        <b>Yatim:</b> {{ $countYatim }} &nbsp;|&nbsp;
                        <b>Piatu:</b> {{ $countPiatu }} &nbsp;|&nbsp;
                        <b>Yatim Piatu:</b> {{ $countYatimPiatu }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>

</html>

