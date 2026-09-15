<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
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

        .group-row td {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            font-size: 10pt;
        }

        .total-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 10pt;
            border-top: 2px solid #0f172a;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <td colspan="15" class="header-title">DATA WALI MURID AKTIF PER KAMPUNG / ZONASI</td>
        </tr>
        <tr>
            <td colspan="15" class="header-sub">MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN</td>
        </tr>
        <tr>
            <td colspan="15" class="header-meta">
                Tahun Pelajaran: {{ $tahunAktif->nama_hijriyah ?? '-' }} H | {{ $tahunAktif->nama_masehi ?? '-' }} M
                &bull;
                Wilayah:
                {{ $filterKampung ? "({$filterKampung->kode}) {$filterKampung->nama_kampung}" : 'Semua Kampung' }}
                &bull;
                Diexport Pada: {{ date('d-m-Y H:i:s') }} WIB
            </td>
        </tr>
        <tr>
            <td colspan="15"></td>
        </tr>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Kampung</th>
                <th>Nama Kampung</th>
                <th>No. Registrasi</th>
                <th>No. KK</th>
                <th>Status Keluarga</th>
                <th>Nama Kepala Keluarga (Wali)</th>
                <th>Kategori Wali</th>
                <th>No. HP / WA</th>
                <th>Alamat Detail</th>
                <th>Jml Anak</th>
                <th>Nama Putra / Putri (Murid)</th>
                <th>L/P</th>
                <th>NISM</th>
                <th>Ruangan / Kelas</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $grandTotalWali = 0;
                $grandTotalMurid = 0;
            @endphp

            @forelse($groupedWalis as $groupNama => $walis)
                @php
                    $totalAnakGroup = $walis->sum(function ($w) {
                        return $w->murids->count();
                    });
                    $grandTotalWali += $walis->count();
                    $grandTotalMurid += $totalAnakGroup;
                @endphp

                <tr class="group-row">
                    <td colspan="15">
                        KAMPUNG: {{ strtoupper($groupNama) }} (Total: {{ $walis->count() }} KK, {{ $totalAnakGroup }}
                        Murid Aktif)
                    </td>
                </tr>

                @foreach ($walis as $wali)
                    @php
                        $children = $wali->murids;
                        $childCount = $children->count();
                        $rowspan = max(1, $childCount);
                    @endphp

                    @if ($childCount > 0)
                        @foreach ($children as $idx => $anak)
                            <tr>
                                @if ($idx === 0)
                                    <td rowspan="{{ $rowspan }}" align="center">{{ $no++ }}</td>
                                    <td rowspan="{{ $rowspan }}" class="num-text" align="center">
                                        {{ $wali->kampung->kode ?? '-' }}</td>
                                    <td rowspan="{{ $rowspan }}">{{ $wali->kampung->nama_kampung ?? '-' }}</td>
                                    <td rowspan="{{ $rowspan }}" class="num-text" align="center">
                                        {{ $wali->no_registrasi ?? '-' }}</td>
                                    <td rowspan="{{ $rowspan }}" class="num-text" align="center">
                                        {{ $wali->no_kk ?? '-' }}</td>
                                    <td rowspan="{{ $rowspan }}" align="center">
                                        {{ $wali->kepala_keluarga ?? 'Ayah' }}</td>
                                    <td rowspan="{{ $rowspan }}"><b>{{ $wali->nama_kepala_keluarga }}</b></td>
                                    <td rowspan="{{ $rowspan }}" align="center">
                                        {{ $wali->is_asatidz || $wali->is_ustadz ? 'Asatidz' : 'Umum' }}</td>
                                    <td rowspan="{{ $rowspan }}" class="num-text" align="center">
                                        {{ $wali->no_hp ?: '-' }}</td>
                                    <td rowspan="{{ $rowspan }}">{{ $wali->alamat_detail ?: '-' }}</td>
                                    <td rowspan="{{ $rowspan }}" align="center">{{ $childCount }}</td>
                                @endif

                                <td>{{ $anak->nama_lengkap }}</td>
                                <td align="center">{{ $anak->jenis_kelamin }}</td>
                                <td class="num-text" align="center">{{ $anak->nism }}</td>
                                <td align="center"><b>{{ $anak->nama_ruangan_aktif }}</b></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td align="center">{{ $no++ }}</td>
                            <td class="num-text" align="center">{{ $wali->kampung->kode ?? '-' }}</td>
                            <td>{{ $wali->kampung->nama_kampung ?? '-' }}</td>
                            <td class="num-text" align="center">{{ $wali->no_registrasi ?? '-' }}</td>
                            <td class="num-text" align="center">{{ $wali->no_kk ?? '-' }}</td>
                            <td align="center">{{ $wali->kepala_keluarga ?? 'Ayah' }}</td>
                            <td><b>{{ $wali->nama_kepala_keluarga }}</b></td>
                            <td align="center">{{ $wali->is_asatidz || $wali->is_ustadz ? 'Asatidz' : 'Umum' }}</td>
                            <td class="num-text" align="center">{{ $wali->no_hp ?: '-' }}</td>
                            <td>{{ $wali->alamat_detail ?: '-' }}</td>
                            <td align="center">0</td>
                            <td colspan="4" align="center" style="color: #94a3b8; font-style: italic;">Tidak ada
                                murid aktif</td>
                        </tr>
                    @endif
                @endforeach
            @empty
                <tr>
                    <td colspan="15" align="center">Tidak ada data wali murid aktif ditemukan.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="6" align="right"><b>TOTAL KESELURUHAN:</b></td>
                <td colspan="4"><b>{{ $grandTotalWali }} Kepala Keluarga (Wali Aktif)</b></td>
                <td align="center"><b>{{ $grandTotalMurid }}</b></td>
                <td colspan="4"><b>{{ $grandTotalMurid }} Murid Aktif</b></td>
            </tr>
        </tbody>
    </table>

</body>

</html>
