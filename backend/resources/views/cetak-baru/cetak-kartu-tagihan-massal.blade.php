<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu SPP</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Setup Print Kertas A4 Landscape Tanpa Margin */
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            /* Warna abu-abu saat preview di browser */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family: Arial, sans-serif;
        }

        .page-wrapper {
            width: 297mm;
            height: 210mm;
            background: white;
            margin: 10mm auto;
            page-break-after: always;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media print {
            body {
                background: white;
            }

            .page-wrapper {
                margin: 0;
                box-shadow: none;
            }
        }

        /* Memastikan tabel memiliki ketebalan border presisi */
        .border-tebal {
            border-width: 1.5px !important;
            border-color: black !important;
        }
    </style>
</head>

<body>

    {{-- CONTOH: Jika data murid Anda lebih dari 4, Anda bisa membungkusnya dengan chunk.
         @foreach ($murids->chunk(4) as $chunkMurid) 
    --}}
    @foreach ($murids->chunk(4) as $chunkMurid)
        <!-- 1 HALAMAN A4 (Berisi 4 Kartu) -->
        <div class="page-wrapper grid grid-cols-2 grid-rows-2 box-border">
            @foreach ($chunkMurid as $murid)
                {{-- @foreach ($murids as $murid) --}}
                <div class="w-[148.5mm] h-[105mm] p-1.5 box-border">
                    <!-- BORDER LUAR TEBAL -->
                    <div class="border-[3px] border-black p-0.5 w-full h-full box-border">
                        <!-- BORDER DALAM TIPIS -->
                        <div class="border-[1.5px] border-black w-full h-full p-2.5 flex gap-2 relative">

                            <!-- ========================================== -->
                            <!-- PANEL KIRI (Informasi Siswa) - 55% Lebar  -->
                            <!-- ========================================== -->
                            <div class="w-[55%] flex flex-col relative h-full">

                                <!-- HEADER MADRASAH -->
                                <div
                                    class="flex items-start justify-between pb-1.5 border-b-[1.5px] border-black mb-2 gap-2">

                                    <!-- KOP Surat Lengkap (Logo + Teks) -->
                                    <div class="flex-1 h-[48px]">
                                        <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Madrasah"
                                            class="w-full h-full object-left object-contain"
                                            style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                    </div>

                                    <!-- Nomor Kartu & Kelas -->
                                    <div class="flex flex-col items-end shrink-0 mt-0.5 pl-2">

                                        <!-- Gambar Barcode -->
                                        <div class="h-[54px] mb-0.5">
                                            {{-- CARA 1: Menggunakan API Generator (Sangat praktis, tapi butuh koneksi internet saat cetak) --}}
                                            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $murid->nism ?? '1047' }}&code=Code128&dpi=96"
                                                alt="Barcode" class="h-full w-auto object-contain"
                                                style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                        </div>
                                        <!-- Label Kelas -->
                                        <div class="text-[10px] font-black leading-none px-1.5 py-0.5">

                                        </div>
                                    </div>

                                </div>

                                <!-- JUDUL KARTU -->
                                <div class="text-center mb-3 mt-1">
                                    <h3 class="text-[10px] font-black tracking-tight">KARTU PEMBAYARAN SYAHRIYAH (SPP)
                                    </h3>
                                    <p class="text-[9px] font-bold">1447-1448 H / 2026-2027 H</p>
                                </div>

                                <!-- DATA IDENTITAS -->
                                <div
                                    class="grid grid-cols-[55px_10px_1fr] gap-y-[4px] text-[10px] font-black uppercase">
                                    <div class="text-blue-700">ID</div>
                                    <div class="text-blue-700">:</div>
                                    <div class="text-blue-700 tracking-wider">{{ $murid->nism ?? '1047' }}</div>

                                    <div>NAMA</div>
                                    <div>:</div>
                                    <div class="text-blue-700">{{ $murid->nama_lengkap ?? 'SYAHRUL MUBAROK' }}</div>

                                    <div>KELAS</div>
                                    <div>:</div>
                                    <div>{{ $murid->kelas ?? '3 TSA (  )' }}</div>

                                    <div>WALI</div>
                                    <div>:</div>
                                    <div>{{ $murid->nama_ayah ?? 'MOH MUSIR' }}</div>

                                    <div>ALAMAT</div>
                                    <div>:</div>
                                    <div class="truncate">
                                        {{ $murid->waliMurid->kampung->nama_kampung ?? 'DEJEH PASAR' }}
                                    </div>

                                    <div>TAGIHAN</div>
                                    <div>:</div>
                                    <div>Rp 25.000,-</div>
                                </div>

                                <!-- TANDA TANGAN (Posisi dipaku di bawah kanan panel kiri) -->
                                <div class="absolute bottom-1 right-1 flex justify-end">
                                    <div class="w-[35mm] flex flex-col items-center text-center">
                                        <!-- Jabatan -->
                                        <p class="text-[6.5px] font-black text-black uppercase mb-0.5">Pengasuh</p>

                                        <!-- QR Code Tanda Tangan Pengasuh -->
                                        <div
                                            class="my-0.5 border border-zinc-300 p-0.5 bg-white rounded-sm flex items-center justify-center [&>svg]:w-[14mm] [&>svg]:h-[14mm]">
                                            @if (!empty($pengasuh?->id))
                                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(45)->generate(
                                                    URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                                ) !!}
                                            @else
                                                <div
                                                    class="w-[14mm] h-[14mm] bg-zinc-100 flex items-center justify-center text-[4px] text-zinc-400 text-center">
                                                    QR KOSONG
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Nama Pengurus -->
                                        <p
                                            class="font-black text-[7.5px] text-zinc-900 border-b border-zinc-600 pb-[2px] w-full mt-0.5 leading-tight uppercase">
                                            {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'NAMA PENGASUH') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- PANEL KANAN (Tabel Pembayaran) - 45% Lebar -->
                            <!-- ========================================== -->
                            <div class="w-[45%] flex flex-col justify-between h-full">
                                <table class="w-full border-collapse border-tebal text-center text-[9px] font-black">
                                    <thead>
                                        <tr>
                                            <th class="border-tebal py-1">BULAN</th>
                                            <th class="border-tebal py-1 w-[26%]">TANGGAL</th>
                                            <th class="border-tebal py-1 w-[32%]">UANG</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($bulanHijriyah as $b)
                                            <tr>
                                                <td class="border-tebal text-left px-1.5 h-[19px]">{{ $b->nama_bulan }}
                                                    {{ $b->tahun_hijriyah }}
                                                </td>
                                                <td class="border-tebal"></td>
                                                <td class="border-tebal"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <!-- CATATAN FOOTER MERAH -->
                                <div class="mt-auto text-left pl-1">
                                    <span class="text-[9px] font-bold text-red-600 tracking-tight">Harap simpan kartu
                                        ini
                                        dengan baik</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach


        </div>
    @endforeach

    <!-- Script Print Otomatis -->
    <script>
        window.onload = function() {
            // Uncomment baris di bawah jika ingin dialog print otomatis terbuka saat halaman dimuat
            window.print();
        }
    </script>
</body>

</html>
