<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Pembayaran_IMNI_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm;
            }

            body {
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .sheet-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                border: none !important;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.8cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div
        class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('pembayaran-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Rekap Kasir (Ctrl+P)</span>
        </button>
    </div>

    <div class="sheet-container">
        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[3px] border-double border-slate-800 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[75px] max-h-[85px] w-auto object-contain">
            </div>
            <div class="text-right leading-snug">
                <h1 class="m-0 text-sm md:text-base font-black text-indigo-700 underline uppercase tracking-wide">
                    PANITIA IMTIHAN NIHA'I (IMNI)
                </h1>
                <h2 class="m-0 text-xs md:text-sm font-extrabold uppercase tracking-wide text-zinc-900">
                    {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                </h2>
                <p class="mt-0.5 text-[10px] font-bold text-zinc-600 uppercase">
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H
                    ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                </p>
                <p class="text-[9px] font-medium text-zinc-500">
                    {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                </p>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="text-center mb-4">
            <h2 class="text-xs md:text-sm font-black uppercase tracking-wider underline">
                REKAPITULASI PEMBAYARAN ADMINISTRASI IMNI
            </h2>
            <p class="text-[10px] font-bold text-zinc-500 mt-0.5">
                @if (isset($selectedTingkat))
                    Tingkat: {{ $selectedTingkat->nama_tingkat }} ({{ $selectedTingkat->kode_tingkat }}) |
                @endif Total Peserta: {{ $pesertas->count() }} Murid | Total Penerimaan: Rp
                {{ number_format($totalTerbayar, 0, ',', '.') }}
            </p>
        </div>

        <!-- TABEL DATA -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full border-collapse border border-zinc-900 text-[10px]">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                        <th class="border border-zinc-900 py-1.5 px-1 w-7">No</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-24">NISM</th>
                        <th class="border border-zinc-900 py-1.5 px-2 text-left">Nama Murid</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-14">Tingkat</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-20 text-right">Tagihan</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-20 text-right">Terbayar</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-20 text-right">Sisa</th>
                        <th class="border border-zinc-900 py-1.5 px-1 w-16">Status</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-24">No. Kwitansi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesertas as $index => $p)
                        @php
                            $bayar = $p->pembayaran;
                            $tagihan = (float) ($bayar?->nominal_tagihan ?? 0);
                            $terbayar = (float) ($bayar?->nominal_bayar ?? 0);
                            $sisa = (float) ($bayar?->sisa_tagihan ?? 0);
                            $status = $bayar?->status_pembayaran ?? 'Belum Ada';
                        @endphp
                        <tr class="hover:bg-zinc-50">
                            <td class="border border-zinc-900 py-1.5 px-1 text-center font-bold">{{ $index + 1 }}
                            </td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-mono font-black">
                                {{ $p->murid?->nism ?? '-' }}</td>
                            <td class="border border-zinc-900 py-1.5 px-2 font-bold uppercase">
                                {{ $p->murid?->nama_lengkap ?? '-' }}</td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center">
                                {{ $p->tingkat?->kode_tingkat ?? '-' }}</td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-right font-mono">Rp
                                {{ number_format($tagihan, 0, ',', '.') }}</td>
                            <td
                                class="border border-zinc-900 py-1.5 px-2 text-right font-mono font-bold text-emerald-800">
                                Rp {{ number_format($terbayar, 0, ',', '.') }}</td>
                            <td
                                class="border border-zinc-900 py-1.5 px-2 text-right font-mono {{ $sisa > 0 ? 'text-amber-800 font-bold' : '' }}">
                                Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                            <td class="border border-zinc-900 py-1.5 px-1 text-center font-bold text-[9px] uppercase">
                                {{ $status }}</td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-mono text-[9px]">
                                {{ $bayar?->no_kwitansi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="border border-zinc-900 py-6 text-center text-zinc-400 font-bold">
                                Belum ada data pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-zinc-100 font-black text-[10px]">
                        <td colspan="4" class="border border-zinc-900 py-2 px-3 text-right uppercase">Total
                            Rekapitulasi:</td>
                        <td class="border border-zinc-900 py-2 px-2 text-right font-mono">Rp
                            {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        <td class="border border-zinc-900 py-2 px-2 text-right font-mono text-emerald-800">Rp
                            {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                        <td class="border border-zinc-900 py-2 px-2 text-right font-mono text-amber-800">Rp
                            {{ number_format($totalSisa, 0, ',', '.') }}</td>
                        <td colspan="2" class="border border-zinc-900"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- TANDA TANGAN -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-6 break-inside-avoid">
            <div>
                <p class="text-zinc-500 text-[10.5px]">Mengetahui,</p>
                <p class="font-bold text-zinc-800 text-xs">Ketua Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">(Tanda
                            Tangan & Stempel)</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase text-xs">
                    {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>
                @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                    <p class="text-[9px] text-zinc-500 font-mono">NIP: {{ $ketuaPanitia->ustadz->nip_ustadz }}</p>
                @endif
            </div>
            <div>
                <p class="text-zinc-500 text-[10.5px]">{{ getSetting('kota_madrasah', 'Bangkalan') }},
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-zinc-800 text-xs">Bendahara Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($bendaharaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id])) !!}
                    @elseif (!empty($bendaharaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz->id])) !!}
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">(Tanda
                            Tangan)</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase text-xs">
                    {{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? 'Bendahara Panitia IMNI' }}
                </p>
                @if ($bendaharaPanitia?->ustadz?->nip_ustadz)
                    <p class="text-[9px] text-zinc-500 font-mono">NIP: {{ $bendaharaPanitia->ustadz->nip_ustadz }}</p>
                @endif
            </div>
        </div>
    </div>

</body>

</html>
