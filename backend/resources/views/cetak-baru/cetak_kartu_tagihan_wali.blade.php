<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Tagihan Wali Murid - {{ $tahunPelajaran->nama_hijriyah ?? 'MDT' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
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

            .no-print {
                display: none !important;
            }
        }

        .border-tebal {
            border-width: 1.5px !important;
            border-color: black !important;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR UNTUK PREVIEW BROWSER -->
    <div
        class="no-print fixed top-4 right-4 z-50 flex items-center gap-2 bg-zinc-900/90 backdrop-blur-md text-white px-4 py-2.5 rounded-2xl shadow-xl border border-zinc-700 text-xs">
        <span class="font-bold text-zinc-300">Total: {{ $walis->count() }} Kartu Tagihan</span>
        <button onclick="window.print()"
            class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 font-bold rounded-xl flex items-center gap-1.5 transition-colors cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak (Print)</span>
        </button>
        <button onclick="window.close()"
            class="px-3 py-1.5 bg-zinc-700 hover:bg-zinc-600 font-bold rounded-xl transition-colors cursor-pointer">
            Tutup
        </button>
    </div>

    @foreach ($walis->chunk(4) as $chunkWali)
        <!-- 1 HALAMAN A4 (Berisi Maksimal 4 Kartu) -->
        <div class="page-wrapper grid grid-cols-2 grid-rows-2 box-border">
            @foreach ($chunkWali as $wali)
                @php
                    $tagihan = $wali->current_tagihan;
                    $nominal = $tagihan?->nominal_tagihan ?? ($selectedMaster?->nominal ?? 0);
                    $namaTagihan =
                        $tagihan?->nama_tagihan_spesifik ?? ($selectedMaster?->nama_tagihan ?? 'IURAN IKHTIBAR 52');
                    $isLunas = $tagihan && $tagihan->status_bayar === 'Lunas';
                    $pembayaran = $tagihan?->pembayaranTagihan;
                    $kodeKampung = $wali->kampung->kode ?? 'A1';
                    $noRegistrasi = $wali->no_registrasi ?? $wali->id;
                    $activeMurids = $wali->murids;
                @endphp
                <div class="w-[148.5mm] h-[105mm] p-1.5 box-border">
                    <!-- BORDER KARTU -->
                    <div
                        class="border-[1.5px] border-sky-400 p-2.5 w-full h-full box-border flex flex-col justify-between bg-white text-zinc-900 relative">

                        <!-- ======================================================= -->
                        <!-- HEADER KARTU (KOP SURAT & NOMOR REGISTRASI WALI)        -->
                        <!-- ======================================================= -->
                        <div class="flex items-center justify-between pb-1.5 border-b-[1.5px] border-black gap-2">
                            <!-- Kiri: Logo & Nama Yayasan / Madrasah -->
                            <div class="flex items-center h-[50px] max-w-[55%]">
                                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Madrasah"
                                    class="h-full w-auto object-left object-contain"
                                    style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            </div>

                            <!-- Kanan: Judul & Kode Registrasi Besar -->
                            <div class="flex flex-col items-end text-right leading-tight">
                                <h2 class="text-[10px] font-black tracking-tight uppercase text-zinc-900">
                                    KARTU TAGIHAN WALI MURID
                                </h2>
                                <h3 class="text-[10px] font-black tracking-tight uppercase text-zinc-900">
                                    {{ $namaTagihan }}
                                </h3>
                                <p class="text-[6.5px] font-semibold text-zinc-500 mt-0.5">
                                    kode Kampung - No Registrasi Wali
                                </p>
                                <p
                                    class="text-[15px] font-black tracking-wider font-mono text-zinc-900 leading-none mt-0.5">
                                    {{ $kodeKampung }} - {{ $noRegistrasi }}
                                </p>
                            </div>
                        </div>

                        <!-- ======================================================= -->
                        <!-- BODY KARTU (2 KOLOM: DATA TAGIHAN & BUKTI TRANSAKSI)    -->
                        <!-- ======================================================= -->
                        <div class="grid grid-cols-2 gap-3.5 my-auto pt-1 flex-1 items-stretch">

                            <!-- --------------------------------------------------- -->
                            <!-- KOLOM KIRI: INFO TAGIHAN, DATA MURID & PENGURUS     -->
                            <!-- --------------------------------------------------- -->
                            <div class="flex flex-col justify-between h-full pr-1">
                                <div>
                                    <!-- Judul Subheader Kolom Kiri -->
                                    <div class="text-center mb-1.5">
                                        <h4 class="text-[8.5px] font-black uppercase tracking-tight leading-tight">
                                            KARTU TAGIHAN IURAN WALI MURID
                                        </h4>
                                        <h5
                                            class="text-[7.5px] font-black uppercase tracking-tight leading-tight text-zinc-700">
                                            {{ $namaTagihan }} TAHUN {{ $tahunPelajaran->nama_hijriyah ?? '' }}
                                        </h5>
                                    </div>

                                    <!-- Identitas Kepala Keluarga -->
                                    <div
                                        class="grid grid-cols-[72px_6px_1fr] text-[7.5px] font-bold gap-y-[1.5px] leading-tight items-baseline">
                                        <span class="text-zinc-700">No Registrasi</span>
                                        <span class="text-zinc-700">:</span>
                                        <span class="font-mono font-black text-zinc-900">{{ $noRegistrasi }}</span>

                                        <span class="text-zinc-700">Kepala Keluarga</span>
                                        <span class="text-zinc-700">:</span>
                                        <span
                                            class="uppercase font-black text-zinc-900 truncate">{{ $wali->nama_kepala_keluarga }}</span>

                                        <span class="text-zinc-700">Kampung</span>
                                        <span class="text-zinc-700">:</span>
                                        <span
                                            class="uppercase font-bold text-zinc-800 truncate">{{ $wali->kampung->nama_kampung ?? '-' }}</span>

                                        <span class="col-span-3 text-zinc-700 font-bold mt-0.5">Nama Anak</span>
                                    </div>

                                    <!-- Tabel Nama Anak / Tanggungan Santri -->
                                    <table class="w-full text-left text-[7.5px] border-collapse mt-0.5 mb-1">
                                        <thead>
                                            <tr class="border-y border-black font-black">
                                                <th class="py-0.5 w-4 text-center">No</th>
                                                <th class="py-0.5 w-14">NISM</th>
                                                <th class="py-0.5">Nama</th>
                                                <th class="py-0.5 text-right">Ruangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $displayedMurids = $activeMurids->take(3);
                                            @endphp
                                            @foreach ($displayedMurids as $idx => $anak)
                                                <tr class="border-b border-zinc-400 font-bold">
                                                    <td class="py-0.5 text-center">{{ $idx + 1 }}</td>
                                                    <td class="py-0.5 font-mono text-[7px]">{{ $anak->nism ?? '-' }}
                                                    </td>
                                                    <td class="py-0.5 uppercase truncate max-w-[85px]">
                                                        {{ $anak->nama_lengkap }}</td>
                                                    <td class="py-0.5 text-right text-[7px]">
                                                        {{ $anak->ruangans->first()?->nama_ruangan ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            @for ($i = count($displayedMurids); $i < 2; $i++)
                                                <tr class="border-b border-zinc-400 h-[14px]">
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>

                                    <!-- Label & Tabel Nama Orang Tua -->
                                    <span class="text-zinc-700 font-bold text-[7.5px] block mt-1">Nama Orang Tua</span>
                                    <table class="w-full text-left text-[7.5px] border-collapse mt-0.5 mb-1">
                                        <thead>
                                            <tr class="border-y border-black font-black">
                                                <th class="py-0.5 w-4 text-center">No</th>
                                                <th class="py-0.5 w-14">Orang Tua</th>
                                                <th class="py-0.5">Nama</th>
                                                <th class="py-0.5 text-right">Status / Keberadaan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $firstMurid = $activeMurids->first();
                                                $namaAyah =
                                                    $firstMurid?->nama_ayah ??
                                                    ($wali->kepala_keluarga === 'Ayah'
                                                        ? $wali->nama_kepala_keluarga
                                                        : null);
                                                $statusAyah = $firstMurid?->status_ayah ?? 'Hidup';
                                                $namaIbu =
                                                    $firstMurid?->nama_ibu ??
                                                    ($wali->kepala_keluarga === 'Ibu'
                                                        ? $wali->nama_kepala_keluarga
                                                        : null);
                                                $statusIbu = $firstMurid?->status_ibu ?? 'Hidup';
                                            @endphp
                                            <tr class="border-b border-zinc-400 font-bold">
                                                <td class="py-0.5 text-center">1</td>
                                                <td class="py-0.5 text-zinc-700 font-semibold">Ayah</td>
                                                <td class="py-0.5 uppercase truncate max-w-[85px]">
                                                    {{ $namaAyah ?: '-' }}</td>
                                                <td
                                                    class="py-0.5 text-right uppercase {{ strtolower($statusAyah) === 'meninggal' ? 'text-rose-600 font-black' : '' }}">
                                                    {{ $statusAyah ?: 'Hidup' }}</td>
                                            </tr>
                                            <tr class="border-b border-zinc-400 font-bold">
                                                <td class="py-0.5 text-center">2</td>
                                                <td class="py-0.5 text-zinc-700 font-semibold">Ibu</td>
                                                <td class="py-0.5 uppercase truncate max-w-[85px]">
                                                    {{ $namaIbu ?: '-' }}</td>
                                                <td
                                                    class="py-0.5 text-right uppercase {{ strtolower($statusIbu) === 'meninggal' ? 'text-rose-600 font-black' : '' }}">
                                                    {{ $statusIbu ?: 'Hidup' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Tagihan Nominal -->
                                    <div
                                        class="grid grid-cols-[72px_6px_1fr] text-[8px] font-black items-baseline mb-1">
                                        <span class="text-zinc-900">Tagihan</span>
                                        <span class="text-zinc-900">:</span>
                                        <span class="font-black text-zinc-900">
                                            Rp {{ number_format($nominal, 0, ',', '.') }},-
                                        </span>
                                    </div>
                                </div>

                                <!-- Tanda Tangan Pengasuh & Sekretaris -->
                                <div class="mt-auto pt-1">
                                    <p class="text-center text-[7px] font-bold mb-0.5">Mengetahui,</p>
                                    <div class="flex justify-around items-end text-center">
                                        <!-- Pengasuh -->
                                        <div class="flex flex-col items-center w-[58px]">
                                            <span class="text-[6.5px] font-bold mb-0.5">Pengasuh</span>
                                            <div
                                                class="w-[28px] h-[28px] bg-white flex items-center justify-center p-0.5 border border-zinc-300 rounded-xs [&>svg]:w-[26px] [&>svg]:h-[26px]">
                                                @if (!empty($pengasuh?->id))
                                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(26)->generate(
                                                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                                                    ) !!}
                                                @else
                                                    <div class="text-[4px] text-zinc-400">QR</div>
                                                @endif
                                            </div>
                                            <span
                                                class="text-[6.5px] font-bold mt-0.5 border-b border-zinc-600 pb-[1px] w-full uppercase truncate">
                                                {{ $pengasuh?->anggota?->nama_lengkap ?? ($pengasuh?->nama ?? 'PENGASUH') }}
                                            </span>
                                        </div>

                                        <!-- Sekretaris -->
                                        <div class="flex flex-col items-center w-[58px]">
                                            <span class="text-[6.5px] font-bold mb-0.5">Sekretaris</span>
                                            <div
                                                class="w-[28px] h-[28px] bg-white flex items-center justify-center p-0.5 border border-zinc-300 rounded-xs [&>svg]:w-[26px] [&>svg]:h-[26px]">
                                                @if (!empty($sekretaris?->id))
                                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(26)->generate(
                                                        URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $sekretaris->id]),
                                                    ) !!}
                                                @else
                                                    <div class="text-[4px] text-zinc-400">QR</div>
                                                @endif
                                            </div>
                                            <span
                                                class="text-[6.5px] font-bold mt-0.5 border-b border-zinc-600 pb-[1px] w-full uppercase truncate">
                                                {{ $sekretaris?->anggota?->nama_lengkap ?? ($sekretaris?->nama ?? 'SEKRETARIS') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- --------------------------------------------------- -->
                            <!-- KOLOM KANAN: BUKTI TRANSAKSI (TUNAI & TABUNGAN)     -->
                            <!-- --------------------------------------------------- -->
                            <div class="flex flex-col justify-between h-full pl-1">
                                <div>
                                    <!-- Judul Subheader Kolom Kanan -->
                                    <div class="text-center mb-1.5">
                                        <h4 class="text-[8.5px] font-black uppercase tracking-tight leading-tight">
                                            Bukti Transaksi
                                        </h4>
                                    </div>

                                    <!-- Section A: Bayar Langsung/Tunai -->
                                    <div class="mb-1.5">
                                        <p class="text-[7.5px] font-black text-zinc-900 mb-0.5">A. Bayar Langsung/Tunai
                                        </p>
                                        <div class="space-y-[2px] text-[7px] font-bold">
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-14 text-zinc-700">Tanggal</span>
                                                <span class="flex-1 text-left font-mono">
                                                    @if ($isLunas && $pembayaran && ($pembayaran->metode_pembayaran === 'Tunai' || !$pembayaran->tabungan_id))
                                                        {{ date('d/m/Y', strtotime($pembayaran->tanggal_bayar)) }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-14 text-zinc-700">Pembayar</span>
                                                <span class="flex-1 text-left uppercase truncate">
                                                    @if ($isLunas && $pembayaran && ($pembayaran->metode_pembayaran === 'Tunai' || !$pembayaran->tabungan_id))
                                                        {{ $pembayaran->nama_pembayar ?? $wali->nama_kepala_keluarga }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-14 text-zinc-700">Jumlah</span>
                                                <span class="flex-1 text-left font-bold">
                                                    @if ($isLunas && $pembayaran && ($pembayaran->metode_pembayaran === 'Tunai' || !$pembayaran->tabungan_id))
                                                        Rp
                                                        {{ number_format($pembayaran->total_bayar ?? $nominal, 0, ',', '.') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section B: Tabungan Murid -->
                                    <div class="mb-1">
                                        <p class="text-[7.5px] font-black text-zinc-900 mb-0.5">B. Tabungan Murid</p>
                                        <div class="space-y-[2px] text-[7px] font-bold">
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-22 text-zinc-700">Tanggal</span>
                                                <span class="flex-1 text-left font-mono">
                                                    @if ($isLunas && $pembayaran && ($pembayaran->metode_pembayaran === 'Tabungan' || $pembayaran->tabungan_id))
                                                        {{ date('d/m/Y', strtotime($pembayaran->tanggal_bayar)) }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-22 text-zinc-700">ID Tabungan</span>
                                                <span class="flex-1 text-left font-mono">
                                                    @if ($isLunas && $pembayaran && $pembayaran->tabungan)
                                                        {{ $pembayaran->tabungan->no_rekening ?? $pembayaran->tabungan->id }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-22 text-zinc-700">Nama Murid/Ruangan</span>
                                                <span class="flex-1 text-left truncate">
                                                    @if ($isLunas && $pembayaran && $pembayaran->tabungan && $pembayaran->tabungan->murid)
                                                        {{ $pembayaran->tabungan->murid->nama_lengkap }}
                                                        ({{ $pembayaran->tabungan->murid->ruangans->first()?->nama_ruangan ?? '-' }})
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-end justify-between border-b border-black pb-[1px]">
                                                <span class="w-22 text-zinc-700">Jumlah</span>
                                                <span class="flex-1 text-left font-bold">
                                                    @if ($isLunas && $pembayaran && ($pembayaran->metode_pembayaran === 'Tabungan' || $pembayaran->tabungan_id))
                                                        Rp
                                                        {{ number_format($pembayaran->total_bayar ?? $nominal, 0, ',', '.') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section C: Cicilan (Maks. 3x Cicilan) -->
                                    <div class="mt-1">
                                        <p class="text-[7.5px] font-black text-zinc-900 mb-0.5">C. Cicilan (Maks. 3x
                                            Cicilan)</p>
                                        <table class="w-full text-left text-[7px] border-collapse mb-0.5">
                                            <thead>
                                                <tr class="border-y border-black font-black">
                                                    <th class="py-0.5 w-12">Cicilan</th>
                                                    <th class="py-0.5 w-14">Tanggal</th>
                                                    <th class="py-0.5">Jumlah</th>
                                                    <th class="py-0.5 text-right w-10">Paraf</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-b border-zinc-400 font-bold h-[12px]">
                                                    <td class="py-0.5 text-zinc-700">Cicilan 1</td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 text-right"></td>
                                                </tr>
                                                <tr class="border-b border-zinc-400 font-bold h-[12px]">
                                                    <td class="py-0.5 text-zinc-700">Cicilan 2</td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 text-right"></td>
                                                </tr>
                                                <tr class="border-b border-zinc-400 font-bold h-[12px]">
                                                    <td class="py-0.5 text-zinc-700">Cicilan 3</td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 font-mono text-[6.5px]"></td>
                                                    <td class="py-0.5 text-right"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tanda Tangan Pembayar, Penarap, Bendahara Panitia, Admin Tabungan -->
                                <div class="mt-auto pt-1">
                                    <div class="grid grid-cols-4 gap-1 text-center text-[6.5px] font-bold">
                                        <!-- Pembayar -->
                                        <div class="flex flex-col items-center">
                                            <span class="mb-0.5">Pembayar</span>
                                            <div class="h-[14px]"></div>
                                            <span class="border-b border-zinc-600 pb-[1px] w-full uppercase truncate">
                                                (....................)
                                            </span>
                                        </div>

                                        <!-- Penarap -->
                                        <div class="flex flex-col items-center">
                                            <span class="mb-0.5">Penarap</span>
                                            <div class="h-[14px]"></div>
                                            <span class="border-b border-zinc-600 pb-[1px] w-full uppercase">
                                                (....................)
                                            </span>
                                        </div>

                                        <!-- Bendahara Panitia -->
                                        <div class="flex flex-col items-center">
                                            <span class="mb-0.5">Bendahara Panitia</span>
                                            <div class="h-[14px]"></div>
                                            <span class="border-b border-zinc-600 pb-[1px] w-full uppercase truncate">
                                                (....................)
                                            </span>
                                        </div>

                                        <!-- Admin Tabungan -->
                                        <div class="flex flex-col items-center">
                                            <span class="mb-0.5">Admin Tabungan</span>
                                            <div class="h-[14px]"></div>
                                            <span class="border-b border-zinc-600 pb-[1px] w-full uppercase">
                                                (....................)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ======================================================= -->
                        <!-- FOOTER KARTU (CATATAN MERAH RESMI)                      -->
                        <!-- ======================================================= -->
                        <div
                            class="text-center text-[6.5px] font-semibold text-red-500 italic pt-1 border-t border-zinc-200">
                            Kartu ini sebagai bukti adanya transaksi pembayaran yang sah.
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <!-- Script Print Otomatis -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
