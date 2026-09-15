<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode SKU Produk - MDT Hidayatus Shibyan</title>
    
    <!-- Bootstrap Icons & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            color: #09090b;
            line-height: 1.2;
        }

        /* Top Action Bar (Hidden on Print) */
        .no-print-bar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e4e4e7;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #9333ea;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #7e22ce;
        }

        .btn-secondary {
            background-color: #f4f4f5;
            color: #3f3f46;
            border: 1px solid #e4e4e7;
        }

        .btn-secondary:hover {
            background-color: #e4e4e7;
        }

        /* Print Container */
        .sheet-wrapper {
            max-width: 210mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 10mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        /* Thermal Roll Wrapper */
        .layout-thermal-wrapper {
            max-width: 58mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 4mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        /* GRID LAYOUTS */
        .grid-3col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4mm;
        }

        .grid-4col {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3mm;
        }

        .grid-5col {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 2.5mm;
        }

        .grid-thermal {
            display: flex;
            flex-direction: column;
            gap: 3mm;
        }

        /* STIKER LABEL ITEM */
        .label-sticker {
            background: #ffffff;
            padding: 2.5mm 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .border-cut {
            border: 1px dashed #a1a1aa;
            border-radius: 6px;
        }

        /* Font Sizes & Sizing by Layout */
        .layout-a4_3col .label-sticker {
            min-height: 29mm;
            max-height: 31mm;
        }
        .layout-a4_3col .lbl-header { font-size: 7pt; font-weight: 800; color: #52525b; letter-spacing: 0.2px; }
        .layout-a4_3col .lbl-name { font-size: 8.5pt; font-weight: 900; color: #09090b; line-height: 1.1; margin: 1px 0; }
        .layout-a4_3col .lbl-svg-wrap { height: 11mm; width: 100%; display: flex; align-items: center; justify-content: center; }
        .layout-a4_3col .lbl-sku { font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; font-weight: 800; color: #3f3f46; }
        .layout-a4_3col .lbl-price { font-family: 'JetBrains Mono', monospace; font-size: 8.5pt; font-weight: 900; color: #047857; }

        .layout-a4_4col .label-sticker {
            min-height: 25mm;
            max-height: 27mm;
            padding: 2mm 2.5mm;
        }
        .layout-a4_4col .lbl-header { font-size: 6pt; font-weight: 800; color: #52525b; }
        .layout-a4_4col .lbl-name { font-size: 7.5pt; font-weight: 900; color: #09090b; line-height: 1.1; margin: 1px 0; }
        .layout-a4_4col .lbl-svg-wrap { height: 9mm; width: 100%; display: flex; align-items: center; justify-content: center; }
        .layout-a4_4col .lbl-sku { font-family: 'JetBrains Mono', monospace; font-size: 6.5pt; font-weight: 800; color: #3f3f46; }
        .layout-a4_4col .lbl-price { font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; font-weight: 900; color: #047857; }

        .layout-a4_5col .label-sticker {
            min-height: 20mm;
            max-height: 22mm;
            padding: 1.5mm 2mm;
        }
        .layout-a4_5col .lbl-header { font-size: 5pt; font-weight: 800; color: #52525b; }
        .layout-a4_5col .lbl-name { font-size: 6.5pt; font-weight: 900; color: #09090b; line-height: 1.05; margin: 0.5px 0; }
        .layout-a4_5col .lbl-svg-wrap { height: 7mm; width: 100%; display: flex; align-items: center; justify-content: center; }
        .layout-a4_5col .lbl-sku { font-family: 'JetBrains Mono', monospace; font-size: 5.5pt; font-weight: 800; color: #3f3f46; }
        .layout-a4_5col .lbl-price { font-family: 'JetBrains Mono', monospace; font-size: 6.5pt; font-weight: 900; color: #047857; }

        .layout-thermal .label-sticker {
            width: 100%;
            min-height: 28mm;
            padding: 2mm 3mm;
            margin-bottom: 2mm;
        }
        .layout-thermal .lbl-header { font-size: 7pt; font-weight: 800; color: #52525b; }
        .layout-thermal .lbl-name { font-size: 8pt; font-weight: 900; color: #09090b; line-height: 1.1; margin: 1px 0; }
        .layout-thermal .lbl-svg-wrap { height: 11mm; width: 100%; display: flex; align-items: center; justify-content: center; }
        .layout-thermal .lbl-sku { font-family: 'JetBrains Mono', monospace; font-size: 7pt; font-weight: 800; color: #3f3f46; }
        .layout-thermal .lbl-price { font-family: 'JetBrains Mono', monospace; font-size: 8.5pt; font-weight: 900; color: #047857; }

        .lbl-footer {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #e4e4e7;
            padding-top: 1px;
            margin-top: 1px;
        }

        .truncate-text {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet-wrapper, .layout-thermal-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 0 !important;
            }

            @page {
                margin: 5mm;
                size: auto;
            }
        }
    </style>
</head>
<body>

    <!-- TOP ACTION BAR -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="javascript:window.close()" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> Tutup
            </a>
            <span style="font-size: 13px; font-weight: 800; color: #71717a;">
                Total: <strong style="color: #9333ea;">{{ $totalLabels }}</strong> Lembar Barcode
                ({{ ucfirst(str_replace('_', ' ', $layout)) }})
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer-fill"></i> Cetak Sekarang (Print)
            </button>
        </div>
    </div>

    <!-- SHEET CONTENT -->
    @php
        $wrapperClass = $layout === 'thermal' ? 'layout-thermal-wrapper layout-thermal' : 'sheet-wrapper layout-' . $layout;
        $gridClass = match($layout) {
            'thermal' => 'grid-thermal',
            'a4_4col' => 'grid-4col',
            'a4_5col' => 'grid-5col',
            default   => 'grid-3col'
        };
    @endphp

    <div class="{{ $wrapperClass }}">
        <div class="{{ $gridClass }}">
            @forelse($labels as $lbl)
                @php $p = $lbl['produk']; @endphp
                <div class="label-sticker {{ $options['show_border'] ? 'border-cut' : '' }}">
                    
                    <!-- Header -->
                    @if($options['show_header'])
                        <div class="lbl-header truncate-text">
                            KOPERASI MDT HIDAYATUS SHIBYAN
                        </div>
                    @endif

                    <!-- Nama Produk -->
                    @if($options['show_name'])
                        <div class="lbl-name truncate-text">
                            {{ $p->nama_produk }}
                        </div>
                    @endif

                    <!-- Barcode SVG -->
                    <div class="lbl-svg-wrap">
                        {!! $lbl['svg'] !!}
                    </div>

                    <!-- Footer: SKU & Harga -->
                    <div class="lbl-footer">
                        @if($options['show_code'])
                            <span class="lbl-sku">{{ $p->kode_produk }}</span>
                        @endif

                        @if($options['show_price'])
                            <span class="lbl-price" style="{{ !$options['show_code'] ? 'width: 100%; text-align: center;' : 'margin-left: auto;' }}">
                                Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #a1a1aa; font-weight: 700;">
                    Tidak ada item barcode yang dipilih untuk dicetak.
                </div>
            @endforelse
        </div>
    </div>

    @if(!empty($options['auto_print']))
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
    @endif

</body>
</html>