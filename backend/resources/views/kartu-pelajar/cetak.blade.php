{{-- <script src="https://cdn.tailwindcss.com"></script> --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Pelajar Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #e4e4e7;
            /* zinc-200 */
        }

        /* KONFIGURASI STANDAR CR80 (ID CARD) */
        .id-card {
            width: 85.6mm;
            height: 54mm;
            background-color: #ffffff;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            break-inside: avoid;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        /* GEOMETRIC BACKGROUND */
        .card-bg-shape-1 {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 20mm;
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            z-index: 1;
        }

        .card-bg-shape-2 {
            position: absolute;
            top: 19mm;
            right: 0;
            left: 0;
            height: 1.5mm;
            background: linear-gradient(90deg, #d97706 0%, #f59e0b 50%, #d97706 100%);
            z-index: 2;
        }

        .watermark {
            position: absolute;
            right: -10mm;
            bottom: -15mm;
            font-size: 80mm;
            color: rgba(4, 120, 87, 0.03);
            z-index: 0;
            transform: rotate(-15deg);
        }

        /* PENGATURAN CETAK KERTAS */
        @media print {
            body {
                background-color: transparent;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 6mm;
                padding: 10mm;
                width: 100%;
            }

            .id-card {
                box-shadow: none;
                border: 0.2px solid #cbd5e1;
                border-radius: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }

            /* Memastikan QR Code SVG ter-render sempurna saat print */
            svg {
                max-width: 100%;
                height: auto;
            }
        }
    </style>
</head>

<body class="p-8">


    <!-- TOOLBAR PRINT -->
    <div
        class="mb-8 flex justify-center gap-4 no-print bg-white p-4 rounded-2xl shadow-sm border border-zinc-200 max-w-2xl mx-auto">
        <div class="flex-1">
            <h3 class="font-black text-zinc-800">Preview Cetak ID Card</h3>
            <p class="text-xs font-bold text-zinc-500">Pastikan pengaturan <b class="text-emerald-600">Background
                    Graphics</b> tercentang saat dialog print muncul.</p>
        </div>
        <button onclick="window.print()"
            class="px-6 py-0 bg-zinc-900 hover:bg-black text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-sm transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-printer-fill"></i> Print
        </button>
    </div>

    <!-- AREA KERTAS -->
    <div class="print-container flex flex-wrap justify-center gap-[6mm] mx-auto max-w-[210mm]">

        @foreach ($murids as $murid)
            <!-- ============================================== -->
            <!-- SISI DEPAN (FRONT)                             -->
            <!-- ============================================== -->
            <div class="id-card">
                <div class="card-bg-shape-1"></div>
                <div class="card-bg-shape-2"></div>
                <i class="bi bi-shield-check watermark"></i>

                <!-- KOP SURAT DINAMIS -->
                <div class="relative z-10 flex items-center gap-3.5 px-4 h-[19mm]">

                    <!-- Logo Madrasah -->
                    <div class="w-12 h-12 flex items-center justify-center shrink-0 drop-shadow-sm">
                        <img src="{{ asset(getSetting('app_logo')) }}" alt="Logo"
                            class="w-full h-full object-contain">
                    </div>

                    <!-- Teks Informasi -->
                    <div class="flex-1 text-white flex flex-col justify-center">
                        <div class="text-[6.5px] font-bold tracking-widest text-emerald-100 uppercase mb-0.5">
                            Kartu Tanda Murid
                        </div>
                        <div class="text-[10px] font-black tracking-wide uppercase leading-tight drop-shadow-sm">
                            MADRASAH DINIYAH TAKMILIYAH
                        </div>
                        <div class="text-[11px] font-black tracking-wide uppercase leading-tight drop-shadow-sm mb-0.5">
                            {{ getSetting('app_name') ?? 'NAMA SEKOLAH' }}
                        </div>
                        <div class="text-[5px] font-medium text-emerald-50 tracking-wider leading-snug">
                            {{ getSetting('app_address') ?? 'Jl. Pendidikan No. 123, Desa Contoh, Kec. Teladan, Kab. Bangkalan' }}
                        </div>
                        <div class="text-[5px] font-medium text-emerald-50 tracking-wider leading-snug">
                            {{ getSetting('app_phone') ?? '081234567890' }},
                            {{ getSetting('app_email') ?? 'email@madrasah.com' }}
                        </div>
                    </div>
                </div>

                <!-- KONTEN DATA IDENTITAS -->
                <div class="relative z-10 flex gap-3 px-4 pt-[3.5mm] h-[30mm] items-start">

                    <!-- Box Foto 3x4 -->
                    <div class="flex flex-col items-center shrink-0 w-[20mm]">

                        <!-- Box Foto 3x4 -->
                        <div
                            class="w-full h-[26mm] border-[1.5px] border-emerald-600 rounded-lg overflow-hidden bg-zinc-100 shadow-[0_2px_10px_rgba(0,0,0,0.1)] flex justify-center items-end relative z-20 p-0.5 mb-[1.5px]">
                            <div class="w-full h-full rounded-md overflow-hidden bg-zinc-200">
                                @if ($murid->foto)
                                    <img src="{{ asset('storage/' . $murid->foto) }}" alt="Foto"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-end justify-center bg-zinc-200 pb-1">
                                        <i class="bi bi-person-fill text-zinc-400 text-5xl leading-none"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Teks Masa Berlaku -->
                        <div class="text-center w-full leading-none mt-0.5">
                            <span
                                class="text-[3.5px] font-bold text-zinc-500 uppercase tracking-widest block mb-[1.5px]">Masa
                                Berlaku</span>
                            <span
                                class="text-[4.5px] font-black text-emerald-700 uppercase tracking-wider block leading-tight">SELAMA
                                MENJADI MURID AKTIF</span>
                        </div>

                    </div>

                    <!-- Label Data -->
                    <div class="flex-1 text-[7.5px] text-zinc-800 flex flex-col justify-between h-[25mm] pt-0.5">
                        <div class="space-y-[1.5px]">
                            <div class="grid grid-cols-[14mm_2mm_auto]">
                                <span class="font-bold text-zinc-500 uppercase tracking-wider">NISM</span>
                                <span class="text-zinc-400">:</span>
                                <span
                                    class="font-black text-emerald-700 tracking-wider">{{ $murid->nism ?? '-' }}</span>
                            </div>
                            <div class="grid grid-cols-[14mm_2mm_auto]">
                                <span class="font-bold text-zinc-500 uppercase tracking-wider">Nama</span>
                                <span class="text-zinc-400">:</span>
                                <span
                                    class="font-black text-[9px] uppercase leading-tight">{{ $murid->nama_lengkap }}</span>
                            </div>
                            <div class="grid grid-cols-[14mm_2mm_auto]">
                                <span class="font-bold text-zinc-500 uppercase tracking-wider">TTL</span>
                                <span class="text-zinc-400">:</span>
                                <span class="font-bold">{{ $murid->tempat_lahir ?? '-' }},
                                    {{ $murid->tanggal_lahir ? date('d-m-Y', strtotime($murid->tanggal_lahir)) : '-' }}</span>
                            </div>
                            <div class="grid grid-cols-[14mm_2mm_auto] items-start">
                                <span class="font-bold text-zinc-500 uppercase tracking-wider">Alamat</span>
                                <span class="text-zinc-400">:</span>
                                <span
                                    class="font-bold leading-tight line-clamp-2 pr-1">{{ $murid->waliMurid->kampung->nama_kampung ?? '-' }}</span>
                            </div>

                        </div>

                        <div class="mt-auto flex flex-col items-start justify-center border-t border-zinc-200 pt-1">

                            <!-- Gambar Barcode (Code 128) -->
                            <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $murid->nism ?? $murid->id }}&includetext=false&scale=2&height=10"
                                class="h-[8.5mm] w-auto max-w-[32mm] object-contain mb-0.5" alt="Barcode Murid">

                            <!-- Teks NISM di bawah Barcode -->
                            <span class="text-[5px] font-black text-zinc-500 tracking-widest text-center">
                                NISM : {{ $murid->nism ?? $murid->id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SISI BELAKANG (BACK)                           -->
            <!-- ============================================== -->
            <div class="id-card flex flex-col justify-between relative bg-white border border-zinc-200">

                <!-- Header Stripe Belakang -->
                <div
                    class="h-[7mm] bg-gradient-to-r from-emerald-800 to-emerald-700 flex items-center justify-between px-3 text-white">
                    <span class="text-[6.5px] font-black tracking-widest uppercase flex items-center gap-1">
                        <i class="bi bi-qr-code-scan text-[8px]"></i> Akses Aplikasi Wali & Ketentuan
                    </span>
                    <span class="text-[5.5px] font-bold text-emerald-100 uppercase tracking-wider">
                        MDT Hidayatus Shibyan
                    </span>
                </div>

                <!-- Konten 2 Kolom -->
                <div class="px-3 pt-1.5 pb-1 flex gap-2.5 items-start flex-1">

                    <!-- KOLOM 1: QR CODE LOGIN WALI MURID (UTAMA) -->
                    <div
                        class="w-[30mm] shrink-0 flex flex-col items-center justify-between border border-emerald-500/30 rounded-md p-1.5 bg-emerald-50/50 shadow-2xs">
                        <div
                            class="text-[5.5px] font-black text-emerald-800 uppercase tracking-wider text-center mb-1 leading-none">
                            <i class="bi bi-phone-fill text-[6px]"></i> QR Login Wali
                        </div>

                        <!-- QR Code Container -->
                        <div
                            class="border border-emerald-600/30 p-1 bg-white rounded shadow-sm flex items-center justify-center [&>svg]:w-[18mm] [&>svg]:h-[18mm]">
                            @if ($murid->qr_login_payload)
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(68)->margin(0)->generate($murid->qr_login_payload) !!}
                            @else
                                <div
                                    class="w-[18mm] h-[18mm] bg-zinc-100 flex items-center justify-center text-[4px] text-zinc-400">
                                    QR KOSONG</div>
                            @endif
                        </div>

                        <div class="text-center mt-1 w-full leading-none">
                            <span class="text-[4.5px] font-extrabold text-zinc-700 block tracking-tight">
                                No. Reg: <b
                                    class="text-emerald-700 font-black">{{ $murid->waliMurid->no_registrasi ?? '-' }}</b>
                            </span>
                            <span class="text-[3.8px] font-medium text-zinc-500 block mt-0.5 leading-tight">
                                Scan di App Android Wali Murid
                            </span>
                        </div>
                    </div>

                    <!-- KOLOM 2: KETENTUAN & TTD PENGASUH -->
                    <div class="flex-1 flex flex-col justify-between h-full pt-0.5">

                        <div>
                            <h4
                                class="text-[6.5px] font-black text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i class="bi bi-shield-lock-fill text-[6px]"></i> Ketentuan Kartu & Akun
                            </h4>
                            <ol
                                class="list-decimal pl-2.5 text-[4.5px] font-medium text-zinc-600 space-y-[1px] leading-tight text-justify pr-1">
                                <li>Kartu ini adalah identitas resmi santri & akses portal digital wali murid.</li>
                                <li>Scan QR Code untuk login otomatis ke aplikasi seluler monitoring santri.</li>
                                <li>Jaga kerahasiaan QR Code ini dari pihak yang tidak berkepentingan.</li>
                                <li>Bila kartu hilang, segera hubungi sekretariat madrasah.</li>
                            </ol>
                        </div>

                        <!-- TTD Pengasuh & Kontak -->
                        <div class="flex justify-between items-end border-t border-zinc-200/80 pt-1 mt-1">
                            <div class="text-[4px] font-medium text-zinc-500 space-y-0.5 leading-tight">
                                <div class="flex items-center gap-1 text-emerald-800 font-bold">
                                    <i class="bi bi-whatsapp"></i> {{ getSetting('app_phone', '0812-3456-7890') }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-globe"></i>
                                    {{ getSetting('app_email', 'mdthidayatusshibyan.sch.id') }}
                                </div>
                            </div>

                            <!-- TTD Pengasuh -->
                            <div class="text-center w-[22mm] shrink-0">
                                <p class="text-[4.5px] font-bold text-zinc-500 leading-tight">Pengasuh,</p>
                                <div
                                    class="my-0.5 border border-zinc-300 p-0.5 bg-white rounded-xs shadow-2xs inline-flex items-center justify-center [&>svg]:w-[7mm] [&>svg]:h-[7mm]">
                                    @if (!empty($pengasuh?->id))
                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(28)->margin(0)->generate(
                                                \Illuminate\Support\Facades\URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                            ) !!}
                                    @else
                                        <span class="text-[3px] text-zinc-300">TTE</span>
                                    @endif
                                </div>
                                <p
                                    class="font-black text-[5px] text-zinc-800 border-b border-zinc-300 pb-0.2 truncate leading-tight">
                                    {{ $pengasuh?->anggota?->nama_lengkap ?? 'Pengasuh MDT' }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer Stripe -->
                <div
                    class="h-[2mm] bg-zinc-100 border-t border-zinc-200 flex items-center justify-between px-3 text-[4px] text-zinc-400">
                    <span>Dokumen Sah MDT Hidayatus Shibyan</span>
                    <span>Tahun Pelajaran Aktif</span>
                </div>
            </div>
        @endforeach

    </div>

</body>

</html>
