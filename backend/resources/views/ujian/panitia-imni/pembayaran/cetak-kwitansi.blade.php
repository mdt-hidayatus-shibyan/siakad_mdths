<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kwitansi_IMNI_{{ $pembayaran->no_kwitansi ?? 'DRAFT' }}_{{ $pembayaran->peserta?->murid?->nama_lengkap ?? 'Murid' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #f1f5f9;
            font-size: 12px;
            -webkit-font-smoothing: antialiased;
        }

        /* ==========================================================================
           1. FLOATING ACTION TOOLBAR (NO PRINT)
           ========================================================================== */
        .no-print {
            display: block;
        }

        .action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 10px 16px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .bar-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-act {
            border: none;
            padding: 7px 14px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
        }

        .btn-primary:hover {
            background: #059669;
        }

        .btn-dark {
            background: rgba(255, 255, 255, 0.15);
            color: #f8fafc;
        }

        .btn-dark:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .btn-toggle.active {
            background: #3b82f6;
            color: #ffffff;
        }

        /* ==========================================================================
           2. TAMPILAN RESI M-BANKING (MOBILE-FIRST & SCREENSHOT FRIENDLY)
           ========================================================================== */
        .mbanking-wrapper {
            max-width: 440px;
            margin: 20px auto 40px auto;
            padding: 0 14px;
        }

        .mbanking-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.07), 0 0 0 1px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            position: relative;
        }

        /* Header Lembaga */
        .mbanking-header {
            padding: 22px 24px 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #f1f5f9;
        }

        .mbanking-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mbanking-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border-radius: 12px;
            background: #ffffff;
            padding: 2px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .mbanking-title-box h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .mbanking-title-box p {
            margin: 2px 0 0 0;
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
        }

        .mbanking-chip-sah {
            font-size: 9.5px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Hero Success Area */
        .mbanking-hero {
            padding: 24px 20px 20px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .mbanking-check-circle {
            width: 62px;
            height: 62px;
            border-radius: 999px;
            background: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            margin-bottom: 14px;
            box-shadow: 0 0 0 8px #d1fae5, 0 8px 16px rgba(16, 185, 129, 0.25);
        }

        .mbanking-check-circle svg {
            width: 32px;
            height: 32px;
            stroke-width: 3.2;
        }

        .mbanking-status-headline {
            margin: 0;
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .mbanking-timestamp {
            margin: 4px 0 16px 0;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }

        .mbanking-amount-box {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 4px;
            margin-bottom: 10px;
        }

        .mbanking-currency {
            font-size: 17px;
            font-weight: 800;
            color: #059669;
        }

        .mbanking-nominal {
            font-size: 32px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.8px;
            line-height: 1;
        }

        .mbanking-badge-lunas {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-lunas-green {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }

        .badge-cicilan-amber {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        /* Pemisah Struk Bertakik (Perforated Ticket Notches) */
        .mbanking-ticket-divider {
            position: relative;
            height: 24px;
            display: flex;
            align-items: center;
            margin: 8px 0;
        }

        .ticket-notch-left {
            position: absolute;
            left: -12px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #f1f5f9;
            box-shadow: inset -2px 0 3px rgba(0, 0, 0, 0.04);
        }

        .ticket-notch-right {
            position: absolute;
            right: -12px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #f1f5f9;
            box-shadow: inset 2px 0 3px rgba(0, 0, 0, 0.04);
        }

        .ticket-dashed-line {
            width: 100%;
            border-bottom: 2px dashed #cbd5e1;
            margin: 0 16px;
        }

        /* Detail List Rows */
        .mbanking-details {
            padding: 12px 24px 20px 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 7px 0;
            gap: 12px;
        }

        .detail-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            flex-shrink: 0;
        }

        .detail-val {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
            word-break: break-word;
        }

        .detail-val.mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #0284c7;
            font-size: 11.5px;
        }

        .detail-val.success {
            color: #059669;
        }

        .detail-val.danger {
            color: #e11d48;
        }

        .inner-line {
            border-bottom: 1px solid #f1f5f9;
            margin: 6px 0;
        }

        .copy-pill {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 1px 6px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            border: none;
            margin-left: 4px;
        }

        .copy-pill:active {
            transform: scale(0.95);
        }

        /* Footer & Keabsahan Digital */
        .mbanking-footer {
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            padding: 18px 24px 22px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .qr-seal-box {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            background: #ffffff;
            padding: 4px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-seal-box svg,
        .qr-seal-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .seal-info {
            flex: 1;
        }

        .seal-tag {
            font-size: 10px;
            font-weight: 900;
            color: #059669;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 2px;
        }

        .seal-desc {
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.35;
            margin: 0;
        }

        .seal-id {
            font-size: 8.5px;
            font-family: monospace;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Toast Feedback */
        #toastNotice {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #0f172a;
            color: #ffffff;
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 10000;
            opacity: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #toastNotice.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* ==========================================================================
           3. DOKUMEN CETAK KERTAS RESMI A5 (DAPAT DI-SWITCH)
           ========================================================================== */
        #printDocumentView {
            display: none;
            max-width: 680px;
            margin: 20px auto;
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            font-size: 11px;
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
            width: 90px;
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
            text-align: center;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sign-name {
            font-weight: 900;
            color: #0f172a;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 10px;
        }

        /* ==========================================================================
           4. PRINT MEDIA STYLES
           ========================================================================== */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                zoom: 78%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            #mBankingView {
                display: none !important;
            }

            #printDocumentView {
                display: block !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
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
        $isLunas = $pembayaran->status_pembayaran === 'Lunas';
        $noKwitansiCetak = $pembayaran->no_kwitansi ?? 'KW-IMNI/' . str_pad($pembayaran->id, 5, '0', STR_PAD_LEFT);
        $tglBayarCetak = $pembayaran->tanggal_bayar ?? now();
        $nominalCetak = $pembayaran->nominal_bayar;
        $sisaCetak = $pembayaran->sisa_tagihan;
    @endphp

    <!-- FLOATING ACTION TOOLBAR -->
    <div class="no-print action-bar">
        <div class="bar-group">
            <button type="button" id="btnToggleMBanking" class="btn-act btn-dark btn-toggle active"
                onclick="switchView('mbanking')">
                <span>📱 Mode M-Banking</span>
            </button>
            <button type="button" id="btnTogglePrintDoc" class="btn-act btn-dark btn-toggle"
                onclick="switchView('printdoc')">
                <span>📄 Dokumen Cetak A5</span>
            </button>
        </div>

        <div class="bar-group">
            <button type="button" class="btn-act btn-dark" onclick="copyWaText()">
                <span>📋 Salin WA</span>
            </button>
            <button type="button" class="btn-act btn-primary" onclick="window.print()">
                <span>🖨️ Cetak</span>
            </button>
            <button type="button" class="btn-act btn-dark" onclick="window.close()" title="Tutup Halaman">
                <span>✕</span>
            </button>
        </div>
    </div>

    <!-- ==========================================================================
         VIEW 1: M-BANKING DIGITAL SLIP RECEIPT (DEFAULT & SCREENSHOT-FRIENDLY)
         ========================================================================== -->
    <div id="mBankingView" class="mbanking-wrapper">
        <div class="mbanking-card" id="screenshotArea">

            <!-- 1. HEADER LEMBAGA -->
            <div class="mbanking-header">
                <div class="mbanking-brand">
                    <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo MDT"
                        class="mbanking-logo">
                    <div class="mbanking-title-box">
                        <h3>MDT HIDAYATUS SHIBYAN</h3>
                        <p>Panitia Imtihan Niha'i (IMNI) {{ $selectedTahun->nama_hijriyah ?? '' }} H</p>
                    </div>
                </div>
                <span class="mbanking-chip-sah">&#10004; SAH</span>
            </div>

            <!-- 2. HERO STATUS & NOMINAL -->
            <div class="mbanking-hero">
                <div class="mbanking-check-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h2 class="mbanking-status-headline">
                    {{ $isLunas ? 'PEMBAYARAN LUNAS' : 'TRANSAKSI BERHASIL' }}
                </h2>
                <p class="mbanking-timestamp">
                    {{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('l, d F Y • H:i') }} WIB
                </p>

                <div class="mbanking-amount-box">
                    <span class="mbanking-currency">Rp</span>
                    <span class="mbanking-nominal">{{ number_format($nominalCetak, 0, ',', '.') }}</span>
                </div>

                <div>
                    @if ($isLunas)
                        <span class="mbanking-badge-lunas badge-lunas-green">&#10004; LUNAS SEPENUHNYA</span>
                    @else
                        <span class="mbanking-badge-lunas badge-cicilan-amber">&#9203; {{ strtoupper($pembayaran->status_pembayaran) }}</span>
                    @endif
                </div>
            </div>

            <!-- 3. PEMISAH TIKET BERTAKIK (TICKET NOTCH) -->
            <div class="mbanking-ticket-divider">
                <div class="ticket-notch-left"></div>
                <div class="ticket-dashed-line"></div>
                <div class="ticket-notch-right"></div>
            </div>

            <!-- 4. DETAIL TRANSAKSI LENGKAP -->
            <div class="mbanking-details">
                <div class="detail-row">
                    <span class="detail-label">No. Referensi</span>
                    <span class="detail-val mono">
                        {{ $noKwitansiCetak }}
                        <button type="button" class="copy-pill" onclick="copyRef('{{ $noKwitansiCetak }}')"
                            title="Salin No Kwitansi">Salin</button>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Jenis Transaksi</span>
                    <span class="detail-val">Biaya Administrasi & Operasional IMNI</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Nama Santri</span>
                    <span class="detail-val">{{ strtoupper($murid?->nama_lengkap ?? '-') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">No. Peserta / Tingkat</span>
                    <span class="detail-val">
                        {{ $peserta?->nomor_peserta ?? '-' }}
                        <span style="color:#64748b; font-weight:normal;">({{ $peserta?->tingkat?->nama_tingkat ?? ($peserta?->tingkat?->kode_tingkat ?? '-') }})</span>
                    </span>
                </div>

                @if ($peserta?->ruanganUjian)
                    <div class="detail-row">
                        <span class="detail-label">Ruangan Ujian</span>
                        <span class="detail-val">{{ $peserta->ruanganUjian->nama_ruangan }}</span>
                    </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Wali Murid</span>
                    <span class="detail-val">{{ strtoupper($namaWali) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Dusun / Alamat</span>
                    <span class="detail-val">{{ strtoupper($dusun) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Metode Pembayaran</span>
                    <span class="detail-val">{{ strtoupper($pembayaran->metode_pembayaran ?? 'TUNAI') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Diterima Oleh</span>
                    <span class="detail-val">{{ $penerimaNama }}</span>
                </div>

                <div class="inner-line"></div>

                <div class="detail-row">
                    <span class="detail-label">Total Tagihan</span>
                    <span class="detail-val">Rp {{ number_format($pembayaran->nominal_tagihan, 0, ',', '.') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Nominal Dibayar</span>
                    <span class="detail-val success">
                        Rp {{ number_format($nominalCetak, 0, ',', '.') }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Sisa Tagihan</span>
                    <span class="detail-val {{ $sisaCetak > 0 ? 'danger' : 'success' }}">
                        {{ $sisaCetak > 0 ? 'Rp ' . number_format($sisaCetak, 0, ',', '.') : 'Rp 0 (LUNAS)' }}
                    </span>
                </div>

                @if ($pembayaran->keterangan || $pembayaran->catatan)
                    <div class="detail-row" style="background:#f8fafc; padding:6px 8px; border-radius:8px; margin-top:4px;">
                        <span class="detail-label">Catatan:</span>
                        <span class="detail-val" style="font-weight:normal; font-style:italic;">
                            {{ $pembayaran->keterangan ?? $pembayaran->catatan }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- 5. FOOTER VERIFIKASI DIGITAL & QR CODE -->
            <div class="mbanking-footer">
                <div class="qr-seal-box">
                    @if (!empty($bendaharaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id]),
                        ) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(52)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id]),
                        ) !!}
                    @else
                        <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Seal MDT">
                    @endif
                </div>
                <div class="seal-info">
                    <div class="seal-tag">&#10004; TERVERIFIKASI SISTEM</div>
                    <p class="seal-desc">
                        Bukti pembayaran digital sah MDT Hidayatus Shibyan. Tangkapan layar ini dapat digunakan sebagai tanda terima resmi.
                    </p>
                    <div class="seal-id">Ref: {{ substr(md5($pembayaran->id . '-' . $nominalCetak . '-' . $noKwitansiCetak), 0, 16) }}</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ==========================================================================
         VIEW 2: DOKUMEN CETAK KERTAS FORMAL A5 (UNTUK ARSIP & PRINT)
         ========================================================================== -->
    <div id="printDocumentView">
        <!-- 1. KOP SURAT RESMI -->
        <div class="kop-surat">
            <div class="kop-left">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="kop-logo" />
            </div>
            <div class="kop-right">
                <h1>BUKTI TANDA TERIMA PEMBAYARAN IMNI</h1>
                <p><strong>No. Kwitansi:</strong> {{ $noKwitansiCetak }}</p>
                <p><strong>Tanggal Bayar:</strong>
                    {{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('d F Y') }}</p>
                <div>
                    @if ($isLunas)
                        <span class="kwitansi-badge">&#10004; LUNAS / SAH</span>
                    @else
                        <span class="kwitansi-badge" style="background: #fffbeb; color: #b45309; border-color: #fde68a;">
                            {{ strtoupper($pembayaran->status_pembayaran) }}
                        </span>
                    @endif
                </div>
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
                        <td class="val">: {{ $peserta?->tingkat?->nama_tingkat ?? ($peserta?->tingkat?->kode_tingkat ?? '-') }}</td>
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
                            style="color: {{ $isLunas ? '#16a34a' : '#d97706' }};">
                            {{ strtoupper($pembayaran->status_pembayaran) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Total Pembayaran:</td>
                        <td class="val text-green font-mono font-black">Rp
                            {{ number_format($nominalCetak, 0, ',', '.') }}</td>
                    </tr>
                    @if ($sisaCetak > 0)
                        <tr>
                            <td>Sisa Tagihan:</td>
                            <td class="val" style="color: #dc2626;">Rp {{ number_format($sisaCetak, 0, ',', '.') }}</td>
                        </tr>
                    @endif
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
                        Rp {{ number_format($nominalCetak, 0, ',', '.') }}
                    </td>
                    <td
                        style="text-align: right; font-weight: bold; font-family: monospace; color: {{ $sisaCetak > 0 ? '#d97706' : '#64748b' }};">
                        Rp {{ number_format($sisaCetak, 0, ',', '.') }}
                    </td>
                    <td
                        style="text-align: center; font-weight: 900; color: {{ $isLunas ? '#16a34a' : '#d97706' }};">
                        {{ strtoupper($pembayaran->status_pembayaran) }}
                    </td>
                </tr>
                <!-- Baris Total -->
                <tr style="background: #f0fdf4; border-top: 2px solid #16a34a;">
                    <td colspan="4" style="text-align: center; font-weight: 900; font-size: 10px;">TOTAL PEMBAYARAN MASUK
                    </td>
                    <td
                        style="text-align: right; color: #16a34a; font-weight: 900; font-size: 11px; font-family: monospace;">
                        Rp {{ number_format($nominalCetak, 0, ',', '.') }}
                    </td>
                    <td colspan="2" style="text-align: center; color: #16a34a; font-weight: 900; font-size: 10px;">
                        {{ $isLunas ? 'LUNAS' : strtoupper($pembayaran->status_pembayaran) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- 4. TERBILANG -->
        <div class="terbilang-box">
            <strong>Terbilang:</strong> <span># {{ terbilang($nominalCetak) }} Rupiah #</span>
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
            {{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('d F Y') }}<br>
            <strong>Mengetahui / Mengesahkan,</strong>
            <div class="footer-signatures">
                <!-- Kolom Ketua Panitia -->
                <div class="signature-box">
                    <span>Ketua Panitia IMNI</span>
                    <div class="sign-spacer">
                        @if (!empty($ketuaPanitia?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                        @elseif (!empty($ketuaPanitia?->ustadz?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                        @else
                            <div style="height: 55px;"></div>
                        @endif
                    </div>
                    <span class="sign-name">{{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}</span>
                    @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                        <span style="font-size: 8.5px; color: #64748b; font-family: monospace;">NIP:
                            {{ $ketuaPanitia->ustadz->nip_ustadz }}</span>
                    @endif
                </div>

                <!-- Kolom Bendahara / Kasir -->
                <div class="signature-box">
                    <span>Bendahara Panitia IMNI</span>
                    <div class="sign-spacer">
                        @if (!empty($bendaharaPanitia?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id])) !!}
                        @elseif (!empty($bendaharaPanitia?->ustadz?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz->id])) !!}
                        @else
                            <div style="height: 55px;"></div>
                        @endif
                    </div>
                    <span
                        class="sign-name">{{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? ($penerimaNama ?? 'Bendahara Panitia') }}</span>
                    @if ($bendaharaPanitia?->ustadz?->nip_ustadz)
                        <span style="font-size: 8.5px; color: #64748b; font-family: monospace;">NIP:
                            {{ $bendaharaPanitia->ustadz->nip_ustadz }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div id="toastNotice">&#10004; Teks berhasil disalin ke clipboard!</div>

    <!-- SCRIPT INTERAKSI M-BANKING & COPY -->
    <script>
        function switchView(mode) {
            const mBankingView = document.getElementById('mBankingView');
            const printDocView = document.getElementById('printDocumentView');
            const btnMBanking = document.getElementById('btnToggleMBanking');
            const btnPrintDoc = document.getElementById('btnTogglePrintDoc');

            if (mode === 'printdoc') {
                mBankingView.style.display = 'none';
                printDocView.style.display = 'block';
                btnMBanking.classList.remove('active');
                btnPrintDoc.classList.add('active');
            } else {
                printDocView.style.display = 'none';
                mBankingView.style.display = 'block';
                btnPrintDoc.classList.remove('active');
                btnMBanking.classList.add('active');
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toastNotice');
            if (!toast) return;
            toast.innerHTML = '&#10004; ' + msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        function copyRef(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Nomor Kwitansi ' + text + ' berhasil disalin!');
            }).catch(() => {
                alert('Tersalin: ' + text);
            });
        }

        function copyWaText() {
            const santri = "{{ $murid?->nama_lengkap ?? '-' }}";
            const noKwitansi = "{{ $noKwitansiCetak }}";
            const nominal = "Rp {{ number_format($nominalCetak, 0, ',', '.') }}";
            const status = "{{ $isLunas ? 'LUNAS (SAH)' : strtoupper($pembayaran->status_pembayaran) }}";
            const tanggal = "{{ \Carbon\Carbon::parse($tglBayarCetak)->translatedFormat('d F Y') }}";
            const sisa = "{{ $sisaCetak > 0 ? 'Rp ' . number_format($sisaCetak, 0, ',', '.') : 'Rp 0 (LUNAS)' }}";

            const waText = 
`*BUKTI PEMBAYARAN DIGITAL IMNI - MDT HIDAYATUS SHIBYAN*
----------------------------------------
*No. Kwitansi :* ${noKwitansi}
*Status       :* ${status}
*Tanggal      :* ${tanggal}
*Nama Santri  :* ${santri}
*Tagihan      :* Biaya Administrasi & Ujian IMNI
*Nominal Bayar:* ${nominal}
*Sisa Tagihan :* ${sisa}
----------------------------------------
_Bukti ini sah dan diterbitkan resmi oleh Panitia IMNI MDT Hidayatus Shibyan._`;

            navigator.clipboard.writeText(waText).then(() => {
                showToast('Format pesan WhatsApp berhasil disalin!');
            }).catch(() => {
                alert('Tersalin!');
            });
        }

        // Auto print hanya jika URL memuat parameter ?print=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            switchView('printdoc');
            setTimeout(() => {
                window.print();
            }, 800);
        }
    </script>
</body>

</html>
