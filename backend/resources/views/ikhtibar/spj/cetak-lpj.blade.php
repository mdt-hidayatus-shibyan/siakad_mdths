<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAPORAN PERTANGGUNGJAWABAN (LPJ) - {{ strtoupper($kegiatan->judul_lengkap) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        @media print {
            body {
                zoom: 85%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            tr {
                page-break-inside: avoid;
            }

            .page-break {
                page-break-before: always;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 2px;
        }

        .kop-kiri {
            width: 100%;
            text-align: left;
        }

        .kop-kiri img {
            max-width: 100%;
            height: auto;
            max-height: 125px;
            display: inline-block;
            object-fit: contain;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 14px;
        }

        .title-lpj {
            text-align: center;
            margin: 10px 0 16px 0;
        }

        .title-lpj h2 {
            font-size: 13pt;
            font-weight: 900;
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .title-lpj p {
            font-size: 10pt;
            margin: 3px 0 0 0;
            color: #334155;
        }

        .section-title {
            font-size: 10pt;
            font-weight: 800;
            color: #0f172a;
            margin: 16px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-title::before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 14px;
            background: #059669;
            border-radius: 2px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin: 8px 0 12px 0;
        }

        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .stat-card .label {
            font-size: 7.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 10.5pt;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            letter-spacing: 0.3px;
        }

        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            color: #1e293b;
            vertical-align: middle;
        }

        .data-table tr.total-row {
            background: #f0fdf4;
            font-weight: 800;
            border-top: 2px solid #16a34a;
        }

        .terbilang-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 9pt;
            margin: 6px 0 12px 0;
            color: #334155;
        }

        .terbilang-box span {
            font-style: italic;
            font-weight: 700;
            color: #0f172a;
            text-transform: capitalize;
        }

        .ttd-container {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .ttd-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
            text-align: center;
        }

        .ttd-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .ttd-role {
            font-size: 8.5pt;
            font-weight: 700;
            color: #475569;
        }

        .ttd-space {
            height: 52px;
            margin: 4px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .ttd-name {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- TOMBOL CETAK & AKSI -->
    <div class="no-print"
        style="position: fixed; top: 15px; right: 15px; background: #1e293b; padding: 8px 14px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 9999; display: flex; gap: 8px;">
        <button onclick="window.print()"
            style="background: #059669; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Cetak Dokumen LPJ
        </button>
        <button onclick="window.close()"
            style="background: #475569; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 12px;">
            ✕ Tutup
        </button>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="header-wrapper">
        <div class="kop-kiri">
            <img src="{{ $kegiatan->kop_logo_url }}" alt="Kop Surat Madrasah">
        </div>
    </div>
    <div class="garis-ganda"></div>

    <!-- Judul Dokumen LPJ -->
    <div class="title-lpj">
        <h2>LAPORAN PERTANGGUNGJAWABAN (LPJ) KEUANGAN</h2>
        <p style="font-weight: 800; text-transform: uppercase;">
            {{ strtoupper($kegiatan->judul_lengkap) }} ({{ $kegiatan->ikhtibar_ke ?? 'Agenda Tahunan' }})
        </p>
        <p style="font-size: 9pt; color: #475569; margin-top: 2px;">
            TAHUN PELAJARAN {{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H /
            {{ $kegiatan->tahunPelajaran?->nama_masehi }} M
        </p>
        @if ($kegiatan->no_sk_panitia)
            <div style="font-size: 8pt; color: #64748b; margin-top: 2px;">
                Dasar Surat Keputusan (SK) Panitia: <strong>{{ $kegiatan->no_sk_panitia }}</strong>
            </div>
        @endif
    </div>

    @php
        $pengasuh =
            $pengasuh ??
            (class_exists(\App\Models\Kepengurusan\Pengurus::class)
                ? \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh')
                : null);
        $namaPengasuh = $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'ABDUL FATTAH');

        $totalPagu = $divisis->sum('nominal_pagu');
        $totalSalur = $divisis->sum(fn($d) => $d->total_tersalurkan);
        $totalTarik = $divisis->sum(fn($d) => $d->total_ditarik_kembali);
        $totalSpj = $pengeluaranList->sum('nominal');
        $sisaKasDivisi = $divisis->sum(fn($d) => $d->sisa_kas_di_divisi);
        $sisaBelumCair = $divisis->sum(fn($d) => $d->sisa_pagu_belum_cair);
    @endphp

    <!-- Card Ringkasan Eksekutif -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="label">Total Plafon Pagu</div>
            <div class="stat-card value">Rp {{ number_format($totalPagu, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Dana Dicairkan</div>
            <div class="stat-card value" style="color: #0284c7;">Rp {{ number_format($totalSalur, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Realisasi SPJ Sah</div>
            <div class="stat-card value" style="color: #b91c1c;">Rp {{ number_format($totalSpj, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Sisa Kas di Divisi</div>
            <div class="stat-card value" style="color: #059669;">Rp {{ number_format($sisaKasDivisi, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- I. NERACA REKAPITULASI FINANSIAL TERPADU -->
    <div class="section-title">I. NERACA REKAPITULASI FINANSIAL TERPADU</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th style="text-align: left;">Uraian Rekapitulasi Kas & Anggaran</th>
                <th style="width: 160px; text-align: right;">Penerimaan / Masuk (Rp)</th>
                <th style="width: 160px; text-align: right;">Pengeluaran / Keluar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center" style="font-weight: bold;">1</td>
                <td>Total Plafon Pagu Anggaran Kepanitiaan (Alokasi Keseluruhan)</td>
                <td align="right">-</td>
                <td align="right" style="font-weight: bold;">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td align="center" style="font-weight: bold;">2</td>
                <td>Realisasi Dana Pagu Tersalurkan / Dicairkan ke Divisi</td>
                <td align="right">-</td>
                <td align="right">Rp {{ number_format($totalSalur, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td align="center" style="font-weight: bold;">3</td>
                <td>Total Realisasi Belanja Operasional (SPJ Sah Terverifikasi)</td>
                <td align="right">-</td>
                <td align="right" style="font-weight: bold; color: #b91c1c;">Rp
                    {{ number_format($totalSpj, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td align="center" style="font-weight: bold;">4</td>
                <td>Total Penarikan Sisa Pagu / Refund Kas ke Bendahara BPH</td>
                <td align="right" style="font-weight: bold; color: #059669;">Rp
                    {{ number_format($totalTarik, 0, ',', '.') }}</td>
                <td align="right">-</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" align="right">SISA KAS FISIK DI DIVISI (BELUM DITARIK):</td>
                <td colspan="2" align="right" style="color: #059669; font-size: 9.5pt;">
                    Rp {{ number_format($sisaKasDivisi, 0, ',', '.') }}
                </td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="2" align="right">SISA ALOKASI PAGU BELUM DICAIRKAN:</td>
                <td colspan="2" align="right" style="color: #64748b; font-size: 9.5pt;">
                    Rp {{ number_format($sisaBelumCair, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="terbilang-box">
        <strong>Terbilang Realisasi SPJ Belanja:</strong>
        <span># {{ terbilang($totalSpj) }} Rupiah #</span>
    </div>

    <!-- II. REKAPITULASI PAGU & REALISASI PER DIVISI -->
    <div class="section-title">II. REKAPITULASI PENYERAPAN PAGU PER DIVISI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th style="text-align: left;">Nama Divisi / Pos Bidang</th>
                <th style="width: 95px; text-align: right;">Pagu (Rp)</th>
                <th style="width: 95px; text-align: right;">Cair (Rp)</th>
                <th style="width: 95px; text-align: right;">SPJ Belanja (Rp)</th>
                <th style="width: 90px; text-align: right;">Sisa Kas (Rp)</th>
                <th style="width: 90px; text-align: right;">Ditarik (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($divisis as $idx => $d)
                <tr>
                    <td align="center" style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $d->nama_divisi }}</strong></td>
                    <td align="right">Rp {{ number_format($d->nominal_pagu, 0, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($d->total_tersalurkan, 0, ',', '.') }}</td>
                    <td align="right" style="font-weight: bold;">Rp
                        {{ number_format($d->total_realisasi_spj, 0, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($d->sisa_kas_di_divisi, 0, ',', '.') }}</td>
                    <td align="right">Rp {{ number_format($d->total_ditarik_kembali, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" align="center">JUMLAH TOTAL</td>
                <td align="right">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totalSalur, 0, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totalSpj, 0, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($sisaKasDivisi, 0, ',', '.') }}</td>
                <td align="right">Rp {{ number_format($totalTarik, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="page-break"></div>

    <!-- III. RINCIAN BUKTI PENGELUARAN (SPJ) DISAHKAN -->
    <div class="section-title">III. DAFTAR RINCIAN NOTA PENGELUARAN (SPJ) SAH TERVERIFIKASI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 85px; text-align: center;">Kode SPJ</th>
                <th style="width: 100px; text-align: left;">Divisi</th>
                <th style="text-align: left;">Uraian Pengeluaran</th>
                <th style="width: 95px; text-align: left;">Toko / Vendor</th>
                <th style="width: 70px; text-align: center;">Tgl Nota</th>
                <th style="width: 90px; text-align: right;">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengeluaranList as $idx => $spj)
                <tr>
                    <td align="center" style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td align="center" style="font-family: monospace; font-weight: bold; font-size: 8pt;">
                        {{ $spj->kode_spj }}</td>
                    <td>{{ $spj->divisi?->nama_divisi ?? 'BPH Inti' }}</td>
                    <td>
                        <strong>{{ $spj->judul_pengeluaran }}</strong>
                        @if ($spj->rincian_item)
                            <br><small style="color: #64748b;">{{ $spj->rincian_item }}</small>
                        @endif
                    </td>
                    <td>{{ $spj->nama_toko_penerima ?: '-' }}</td>
                    <td align="center">{{ $spj->tanggal_nota?->format('d/m/Y') }}</td>
                    <td align="right" style="font-weight: bold;">Rp {{ number_format($spj->nominal, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" align="center" style="padding: 20px; color: #94a3b8; font-style: italic;">
                        Belum ada data pengeluaran SPJ yang disetujui.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" align="right">TOTAL REALISASI BELANJA OPERASIONAL:</td>
                <td align="right" style="font-size: 9.5pt;">Rp {{ number_format($totalSpj, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- LEMBAR PENGESAHAN -->
    <div class="ttd-container">
        <p style="text-align: center; font-size: 9pt; margin-bottom: 5px; color: #475569;">
            Somorkoneng, {{ now()->translatedFormat('d F Y') }}
        </p>
        <p
            style="text-align: center; font-weight: 800; font-size: 9.5pt; margin-bottom: 10px; text-transform: uppercase;">
            PANITIA PELAKSANA {{ strtoupper($kegiatan->judul_lengkap) }}
        </p>

        <div class="ttd-grid">
            <div class="ttd-box">
                <span class="ttd-role">Ketua Panitia,</span>
                <div class="ttd-space">
                    @if (!empty($ketua?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $ketua->id]),
                        ) !!}
                    @elseif ($ketua && $ketua->ttd_utama)
                        <img src="{{ $ketua->ttd_utama }}" style="height: 46px;" alt="TTD Ketua">
                    @endif
                </div>
                <strong
                    class="ttd-name">{{ $ketua ? $ketua->nama_tampil : '..................................' }}</strong>
            </div>

            <div class="ttd-box">
                <span class="ttd-role">Sekretaris Panitia,</span>
                <div class="ttd-space">
                    @if (!empty($sekretaris?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $sekretaris->id]),
                        ) !!}
                    @elseif ($sekretaris && $sekretaris->ttd_utama)
                        <img src="{{ $sekretaris->ttd_utama }}" style="height: 46px;" alt="TTD Sekretaris">
                    @endif
                </div>
                <strong
                    class="ttd-name">{{ $sekretaris ? $sekretaris->nama_tampil : '..................................' }}</strong>
            </div>

            <div class="ttd-box">
                <span class="ttd-role">Bendahara Panitia,</span>
                <div class="ttd-space">
                    @if (!empty($bendahara?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(46)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'panitia', 'id' => $bendahara->id]),
                        ) !!}
                    @elseif ($bendahara && $bendahara->ttd_utama)
                        <img src="{{ $bendahara->ttd_utama }}" style="height: 46px;" alt="TTD Bendahara">
                    @endif
                </div>
                <strong
                    class="ttd-name">{{ $bendahara ? $bendahara->nama_tampil : '..................................' }}</strong>
            </div>
        </div>

        <div style="text-align: center; margin-top: 25px; page-break-inside: avoid;">
            <span style="font-size: 9pt; color: #475569;">Mengetahui & Menyetujui,</span><br>
            <strong style="font-size: 9.5pt; text-transform: uppercase;">Pengasuh MDT Hidayatus Shibyan</strong>
            <div
                style="height: 55px; margin: 5px auto; display: flex; align-items: center; justify-content: center; position: relative; width: 220px;">
                @if (!empty($pengasuh?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @endif
                @if (!empty($pengasuh?->id))
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(
                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                    ) !!}
                @endif
                @if ($kegiatan->stempel_kegiatan)
                    <img src="{{ $kegiatan->stempel_url }}"
                        style="position: absolute; height: 60px; opacity: 0.85; pointer-events: none;"
                        alt="Stempel Panitia">
                @endif
            </div>
            <strong style="text-decoration: underline; font-size: 10pt; text-transform: uppercase;">
                {{ $namaPengasuh }}
            </strong>
        </div>
    </div>
</body>

</html>
