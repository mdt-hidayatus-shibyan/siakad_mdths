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
            <div class="id-card flex flex-col justify-between relative bg-zinc-50">

                <div class="absolute left-0 top-0 bottom-0 w-[3mm] bg-emerald-700"></div>

                <div class="pl-[7mm] pr-4 pt-4 relative z-10">
                    <h4
                        class="text-[9px] font-black text-emerald-800 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                        <i class="bi bi-info-square-fill"></i> Ketentuan Kartu
                    </h4>
                    <ol
                        class="list-decimal pl-3 text-[5px] font-bold text-zinc-600 space-y-[1.5px] leading-snug text-justify pr-2">
                        <li>Kartu ini adalah kartu identitas resmi Murid dan berlaku selama yang bersangkutan berstatus
                            aktif sebagai Murid.</li>
                        <li>Wajib dibawa, dijaga, dan dikenakan selama berada di lingkungan / mengikuti kegiatan
                            madrasah.</li>
                        <li>Kartu tidak dapat dipindahtangankan atau dipinjamkan kepada orang lain dengan alasan apapun.
                        </li>
                        <li>Apabila kartu hilang atau rusak, harap melapor ke bagian Administrasi untuk diterbitkan
                            kartu pengganti.</li>
                        <li>Barang siapa menemukan kartu ini, dimohon kebijaksanaannya untuk mengembalikan ke alamat
                            resmi madrasah.</li>
                    </ol>
                </div>

                <!-- TTD PENGASUH DAN QR CODE TTE -->
                <div class="pl-[7mm] pr-4 pb-3 flex justify-between items-end relative z-10">

                    <div class="text-[5px] font-bold text-zinc-400 space-y-0.5">
                        <!-- Baris Email -->
                        <div class="flex items-center gap-1">
                            <i class="bi bi-envelope-fill"></i>
                            {{ getSetting('app_email') ?? 'info@madrasah.sch.id' }}
                        </div>

                        <!-- Baris WhatsApp -->
                        <div class="flex items-center gap-1">
                            <i class="bi bi-whatsapp"></i>
                            {{ getSetting('app_phone') ?? '0812-3456-7890' }}
                        </div>
                    </div>

                    <!-- Area Verifikasi Tanda Tangan Elektronik -->
                    <div class="w-[35mm] flex flex-col items-center text-center">
                        <p class="text-[5.5px] font-bold text-zinc-600 mb-0.5">Bangkalan,
                            {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                        <p class="text-[6.5px] font-black text-emerald-800 uppercase mb-0.5">Pengasuh</p>

                        <!-- QR Code Tanda Tangan Pengasuh -->
                        <div
                            class="my-0.5 border border-zinc-300 p-0.5 bg-white rounded-sm shadow-sm flex items-center justify-center [&>svg]:w-[12mm] [&>svg]:h-[12mm]">
                            @if (!empty($pengasuh?->id))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(45)->margin(0)->generate(
                                        \Illuminate\Support\Facades\URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                    ) !!}
                            @else
                                <div
                                    class="w-[12mm] h-[12mm] bg-zinc-100 flex items-center justify-center text-[4px] text-zinc-400 text-center">
                                    QR KOSONG</div>
                            @endif
                        </div>

                        <!-- Nama Pengurus (Dibuat wrap agar tidak terpotong) -->
                        <p
                            class="font-black text-[7.5px] text-zinc-900 border-b border-zinc-400 pb-[2px] w-full mt-0.5 leading-tight">
                            {{ $pengasuh?->anggota?->nama_lengkap ?? 'Nama Pengasuh Belum Diatur' }}
                        </p>
                    </div>
                </div>

                <!-- Motif Halus di Background -->
                <i class="bi bi-qr-code absolute left-[5mm] bottom-[5mm] text-[30mm] text-zinc-200/50 z-0"></i>
            </div>
        @endforeach

    </div>

</body>

</html>
