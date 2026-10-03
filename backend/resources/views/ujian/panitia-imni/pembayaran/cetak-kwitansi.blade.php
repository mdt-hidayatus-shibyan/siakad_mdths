<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi_IMNI_{{ $pembayaran->no_kwitansi ?? 'DRAFT' }}_{{ $pembayaran->peserta?->murid?->nama_lengkap }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }

        @media print {
            body {
                zoom: 78%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif;
            padding: 10px 15px;
            margin: 0 auto;
            color: #1e293b;
            font-size: 11px;
            background: #fff;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            max-width: 75%;
            height: auto;
            max-height: 110px;
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
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .kop-right p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #475569;
        }

        .kwitansi-badge {
            display: inline-block;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 11px;
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .info-box {
            width: 48%;
        }

        .info-title {
            font-weight: 900;
            color: #15803d;
            font-size: 9.5px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            color: #475569;
            vertical-align: top;
            font-size: 10.5px;
        }

        .info-table td:first-child {
            width: 85px;
        }

        .info-table .val {
            font-weight: bold;
            color: #0f172a;
        }

        .info-table.right-align td {
            text-align: right;
        }

        .info-table.right-align td:first-child {
            width: auto;
            padding-right: 8px;
        }

        .text-green {
            color: #16a34a !important;
            font-size: 11.5px;
        }

        .table-title {
            font-weight: 900;
            color: #1e293b;
            font-size: 10.5px;
            text-align: center;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-title span {
            color: #15803d;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 7px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }

        .terbilang-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 10px;
            margin-bottom: 10px;
            color: #334155;
        }

        .terbilang-box span {
            font-style: italic;
            font-weight: 700;
            color: #0f172a;
            text-transform: capitalize;
        }

        .notes-box {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 6px 10px;
            border-radius: 0 6px 6px 0;
            font-size: 9.5px;
            margin-bottom: 14px;
            color: #1e40af;
            line-height: 1.35;
        }

        .signature-area {
            width: 100%;
            margin-top: 8px;
            text-align: right;
            font-size: 10px;
        }

        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            text-align: center;
        }

        .signature-box {
            width: 44%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-box span:first-child {
            font-weight: 700;
            color: #475569;
            font-size: 9.5px;
        }

        .sign-spacer {
            height: 55px;
            margin: 4px 0;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 10px;
        }
    </style>
</head>

<body>

    @php
        $murid = $pembayaran->peserta?->murid ?? $pembayaran->murid;
        $peserta = $pembayaran->peserta;
        $namaWali = $murid?->waliMurid?->nama_wali ?? ($murid?->nama_ayah ?? ($pembayaran->nama_penyetor ?? '-'));
        $dusun = $murid?->dusun ?? ($murid?->waliMurid?->kampung?->nama_kampung ?? ($murid?->waliMurid?->desa ?? '-'));
        $penerimaNama = $pembayaran->penerima?->nama_lengkap ?? ($pembayaran->penerima?->name ?? 'Kasir Panitia IMNI');
    @endphp

    <!-- FLOATING ACTION BAR -->
    <div
        class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/95 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('pembayaran-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Kasir</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Kwitansi (Ctrl+P)</span>
        </button>
    </div>

    <!-- 1. KOP SURAT RESMI -->
    <div class="kop-surat">
        <div class="kop-left">
            <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                alt="Logo Madrasah" class="kop-logo" />
        </div>
        <div class="kop-right">
            <h1>BUKTI TANDA TERIMA PEMBAYARAN IMNI</h1>
            <p><strong>No. Kwitansi:</strong> {{ $pembayaran->no_kwitansi ?? 'IMNI/DRAFT' }}</p>
            <p><strong>Tanggal Bayar:</strong>
                {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar ?? now())->translatedFormat('d F Y') }}</p>

        </div>
    </div>

    <!-- 2. INFORMASI PEMBAYAR & TRANSAKSI -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-title">Diterima Dari:</div>
            <table class="info-table">
                <tr>
                    <td>Nama Murid</td>
                    <td class="val">: {{ strtoupper($murid?->nama_lengkap ?? '-') }}</td>
                </tr>
                <tr>
                    <td>No. Peserta</td>
                    <td class="val">: {{ $peserta?->nomor_peserta ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NISM</td>
                    <td class="val">: {{ $murid?->nism ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jenjang / Tingkat</td>
                    <td class="val">: {{ $peserta?->tingkat?->kode_tingkat ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td>Ruangan Ujian</td>
                    <td class="val">: {{ $peserta?->ruanganUjian?->nama_ruangan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Wali Murid</td>
                    <td class="val">: {{ strtoupper($namaWali) }}</td>
                </tr>
                <tr>
                    <td>Dusun / Alamat</td>
                    <td class="val">: {{ strtoupper($dusun) }}</td>
                </tr>
            </table>
        </div>

        <div class="info-box">
            <div class="info-title" style="text-align: right;">Rincian Transaksi:</div>
            <table class="info-table right-align">
                <tr>
                    <td>Metode Pembayaran:</td>
                    <td class="val" style="text-transform: uppercase;">
                        {{ $pembayaran->metode_pembayaran ?? 'TUNAI' }}
                    </td>
                </tr>
                <tr>
                    <td>Penyetor / Wali:</td>
                    <td class="val" style="text-transform: uppercase;">
                        {{ $pembayaran->nama_penyetor ?? ($namaWali ?? '-') }}
                    </td>
                </tr>
                <tr>
                    <td>Diterima Oleh:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $penerimaNama }}</td>
                </tr>
                <tr>
                    <td>Status Kelunasan:</td>
                    <td class="val"
                        style="color: {{ $pembayaran->status_pembayaran === 'Lunas' ? '#16a34a' : '#d97706' }};">
                        {{ strtoupper($pembayaran->status_pembayaran) }}
                    </td>
                </tr>
                <tr>
                    <td>Total Pembayaran:</td>
                    <td class="val text-green font-mono font-black">Rp
                        {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. TABEL RINCIAN ITEM PEMBAYARAN -->
    <div class="table-title">Rincian Item Pembayaran: <span>Imtihan Niha'i (IMNI)</span></div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th>Deskripsi Tagihan</th>
                <th style="text-align: center; width: 110px;">Tahun Pelajaran</th>
                <th style="text-align: right; width: 85px;">Tagihan (Rp)</th>
                <th style="text-align: right; width: 85px;">Terbayar (Rp)</th>
                <th style="text-align: right; width: 85px;">Sisa (Rp)</th>
                <th style="text-align: center; width: 65px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td style="font-weight: 600;">
                    Biaya Administrasi & Operasional IMNI
                    @if ($peserta?->tingkat)
                        <span style="font-size: 9px; color: #64748b; font-weight: normal;">(Tingkat
                            {{ $peserta->tingkat->nama_tingkat }})</span>
                    @endif
                </td>
                <td style="text-align: center; font-weight: bold; color: #475569;">
                    {{ $selectedTahun->nama_hijriyah ?? '-' }} H
                </td>
                <td style="text-align: right; font-weight: bold; font-family: monospace;">
                    Rp {{ number_format($pembayaran->nominal_tagihan, 0, ',', '.') }}
                </td>
                <td style="text-align: right; font-weight: bold; font-family: monospace; color: #16a34a;">
                    Rp {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }}
                </td>
                <td
                    style="text-align: right; font-weight: bold; font-family: monospace; color: {{ $pembayaran->sisa_tagihan > 0 ? '#d97706' : '#64748b' }};">
                    Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}
                </td>
                <td
                    style="text-align: center; font-weight: 900; color: {{ $pembayaran->status_pembayaran === 'Lunas' ? '#16a34a' : '#d97706' }};">
                    {{ strtoupper($pembayaran->status_pembayaran) }}
                </td>
            </tr>
            <!-- Baris Total -->
            <tr style="background: #f0fdf4; border-top: 2px solid #16a34a;">
                <td colspan="4" style="text-align: center; font-weight: 900; font-size: 10px;">TOTAL PEMBAYARAN MASUK
                </td>
                <td
                    style="text-align: right; color: #16a34a; font-weight: 900; font-size: 11px; font-family: monospace;">
                    Rp {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }}
                </td>
                <td colspan="2" style="text-align: center; color: #16a34a; font-weight: 900; font-size: 10px;">
                    {{ $pembayaran->status_pembayaran === 'Lunas' ? 'LUNAS' : strtoupper($pembayaran->status_pembayaran) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 4. TERBILANG -->
    <div class="terbilang-box">
        <strong>Terbilang:</strong> <span># {{ terbilang($pembayaran->nominal_bayar) }} Rupiah #</span>
    </div>

    <!-- 5. CATATAN KETERANGAN -->
    <div class="notes-box">
        <strong>Catatan:</strong> Kwitansi ini diterbitkan resmi oleh Panitia Imtihan Niha'i (IMNI) MDT Hidayatus
        Shibyan sebagai bukti pembayaran administrasi yang sah. Harap disimpan dengan baik.
        @if ($pembayaran->keterangan || $pembayaran->catatan)
            <br><em>Catatan Kasir: {{ $pembayaran->keterangan ?? $pembayaran->catatan }}</em>
        @endif
    </div>

    <!-- 6. TANDA TANGAN -->
    <div class="signature-area text-center">
        {{ getSetting('kota_madrasah', 'Bangkalan') }},
        {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar ?? now())->translatedFormat('d F Y') }}<br>
        <strong>Mengetahui / Mengesahkan,</strong>
        <div class="footer-signatures">
            <!-- Kolom Ketua Panitia -->
            <div class="signature-box">
                <span>Ketua Panitia IMNI</span>
                <div class="min-h-[55px] flex items-center justify-center my-1">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                    @else
                        <div class="sign-spacer"></div>
                    @endif
                </div>
                <span class="sign-name">{{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}</span>
                @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                    <span class="text-[8.5px] text-zinc-500 font-mono">NIP:
                        {{ $ketuaPanitia->ustadz->nip_ustadz }}</span>
                @endif
            </div>

            <!-- Kolom Bendahara / Kasir -->
            <div class="signature-box">
                <span>Bendahara Panitia IMNI</span>
                <div class="min-h-[55px] flex items-center justify-center my-1">
                    @if (!empty($bendaharaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id])) !!}
                    @elseif (!empty($bendaharaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz->id])) !!}
                    @else
                        <div class="sign-spacer"></div>
                    @endif
                </div>
                <span
                    class="sign-name">{{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? ($pembayaran->penerima?->nama_lengkap ?? 'Bendahara Panitia') }}</span>
                @if ($bendaharaPanitia?->ustadz?->nip_ustadz)
                    <span class="text-[8.5px] text-zinc-500 font-mono">NIP:
                        {{ $bendaharaPanitia->ustadz->nip_ustadz }}</span>
                @endif
            </div>
        </div>
    </div>

</body>

</html>
