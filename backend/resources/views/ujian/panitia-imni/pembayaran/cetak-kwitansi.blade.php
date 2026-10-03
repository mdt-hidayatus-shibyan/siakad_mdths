<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi_IMNI_{{ $pembayaran->no_kwitansi ?? 'DRAFT' }}_{{ $pembayaran->murid?->nama_lengkap }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A5 landscape;
                margin: 0.6cm;
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
                border: 2px solid #0f172a !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.8cm 1cm;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 2px solid #18181b;
            position: relative;
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('pembayaran-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Kasir</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Kwitansi (Ctrl+P)</span>
        </button>
    </div>

    <div class="sheet-container">
        <!-- WATERMARK BG -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
            <img src="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Watermark" class="w-72">
        </div>

        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[2px] border-slate-800 pb-2.5 mb-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[60px] max-h-[65px] w-auto object-contain">
                <div>
                    <h1 class="m-0 text-xs font-black text-indigo-700 underline uppercase tracking-wide">
                        PANITIA IMTIHAN NIHA'I (IMNI)
                    </h1>
                    <h2 class="m-0 text-xs font-extrabold uppercase tracking-wide text-zinc-900">
                        {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                    </h2>
                    <p class="text-[9px] font-bold text-zinc-600 uppercase">
                        Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                    </p>
                    <p class="text-[8.5px] font-medium text-zinc-500">
                        {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                    </p>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-block px-3 py-1 bg-emerald-50 border border-emerald-600/30 rounded-lg text-right">
                    <h2 class="text-xs font-black uppercase tracking-wider text-emerald-800 leading-tight">
                        TANDA TERIMA IMNI
                    </h2>
                    <p class="text-[10px] font-mono font-black text-zinc-800 leading-tight mt-0.5">
                        {{ $pembayaran->no_kwitansi ?? 'IMNI/DRAFT' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- CONTENT KWITANSI -->
        <div class="grid grid-cols-12 gap-y-2.5 text-xs text-zinc-800 relative z-10 mb-4">
            <!-- Diterima Dari -->
            <div class="col-span-3 font-bold text-zinc-500">Telah Diterima Dari</div>
            <div class="col-span-9 font-black uppercase text-zinc-900 flex items-center gap-2">
                <span>: {{ $pembayaran->murid?->nama_lengkap ?? '-' }}</span>
                <span class="text-[10px] font-mono font-normal px-2 py-0.2 rounded bg-zinc-100 text-zinc-700">
                    No. Ujian: {{ $pembayaran->peserta?->nomor_peserta ?? '-' }}
                </span>
            </div>

            <!-- Identitas Murid -->
            <div class="col-span-3 font-bold text-zinc-500">Identitas / Kelas</div>
            <div class="col-span-9 text-zinc-700">
                : NISM: <span class="font-mono font-semibold">{{ $pembayaran->murid?->nism ?? '-' }}</span>
                | Jenjang: <span class="font-bold">{{ $pembayaran->peserta?->tingkat?->nama_tingkat ?? '-' }} ({{ $pembayaran->peserta?->level?->nama_level ?? '-' }})</span>
                | Ruang: <span class="font-bold text-emerald-800">{{ $pembayaran->peserta?->ruanganUjian?->nama_ruangan ?? '-' }}</span>
            </div>

            <!-- Nama Penyetor -->
            <div class="col-span-3 font-bold text-zinc-500">Penyetor / Wali</div>
            <div class="col-span-9 font-semibold text-zinc-800">
                : {{ $pembayaran->nama_penyetor ?? ($pembayaran->murid?->nama_lengkap ?? '-') }}
                @if ($pembayaran->metode_pembayaran)
                    <span class="text-[10px] text-zinc-500 font-normal">({{ $pembayaran->metode_pembayaran }})</span>
                @endif
            </div>

            <!-- Uraian Pembayaran -->
            <div class="col-span-3 font-bold text-zinc-500">Untuk Pembayaran</div>
            <div class="col-span-9 font-medium text-zinc-800">
                : <span class="font-bold">Biaya Administrasi & Operasional Imtihan Nihai (IMNI)</span>
                {{ $pembayaran->keterangan ? ' - ' . $pembayaran->keterangan : '' }}
            </div>

            <!-- Terbilang Box -->
            <div class="col-span-3 font-bold text-zinc-500">Terbilang</div>
            <div class="col-span-9">
                <div class="bg-zinc-50 border border-zinc-300 border-dashed rounded-lg px-3 py-1.5 font-bold italic text-zinc-800 text-[11px]">
                    # {{ ucwords(terbilang($pembayaran->nominal_bayar)) }} Rupiah #
                </div>
            </div>

            <!-- Jumlah Nominal Box -->
            <div class="col-span-3 font-bold text-zinc-500 self-center">Jumlah Pembayaran</div>
            <div class="col-span-9 flex items-center gap-4">
                <div class="bg-emerald-50 border-2 border-emerald-600 rounded-xl px-4 py-1.5 text-base font-black font-mono text-emerald-800 inline-block">
                    Rp {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }},-
                </div>

                <!-- Rincian Status Kelunasan -->
                <div class="text-[10px] text-zinc-600 bg-zinc-50 rounded-lg p-2 border border-zinc-200 flex-1 grid grid-cols-3 gap-2 text-center">
                    <div>
                        <span class="text-zinc-400 block">Total Tagihan:</span>
                        <span class="font-mono font-bold">Rp {{ number_format($pembayaran->nominal_tagihan, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block">Sisa Tagihan:</span>
                        <span class="font-mono font-bold {{ $pembayaran->sisa_tagihan > 0 ? 'text-amber-600' : 'text-zinc-700' }}">
                            Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block">Status:</span>
                        <span class="font-black uppercase {{ $pembayaran->status_pembayaran === 'Lunas' ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $pembayaran->status_pembayaran }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN 3 KOLOM -->
        <div class="grid grid-cols-3 gap-4 text-center text-[10px] mt-3 pt-3 border-t border-zinc-200 break-inside-avoid">
            <!-- Kolom 1: Penyetor -->
            <div>
                <p class="text-zinc-400 font-semibold">Tanda Tangan Penyetor,</p>
                <div class="min-h-[55px] flex items-center justify-center text-zinc-300 italic text-[9px] my-1">
                    (....................)
                </div>
                <p class="font-bold text-zinc-800 uppercase">
                    {{ $pembayaran->nama_penyetor ?? 'Wali Murid' }}
                </p>
            </div>

            <!-- Kolom 2: Mengetahui Ketua Panitia -->
            <div>
                <p class="text-zinc-400 font-semibold">Mengetahui, Ketua Panitia</p>
                <div class="min-h-[55px] flex items-center justify-center my-1">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                        ) !!}
                    @else
                        <div class="h-12 flex items-center justify-center text-zinc-300 italic text-[9px]">(Tanda Tangan & Stempel)</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase">
                    {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>
            </div>

            <!-- Kolom 3: Bendahara Panitia IMNI -->
            <div>
                <p class="text-zinc-400 font-semibold">{{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->translatedFormat('d F Y') : date('d F Y') }}</p>
                <p class="font-bold text-zinc-800">Bendahara Panitia IMNI</p>
                <div class="min-h-[55px] flex items-center justify-center my-1">
                    @if (!empty($bendaharaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($bendaharaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz->id])
                        ) !!}
                    @else
                        <div class="h-12 flex items-center justify-center text-zinc-300 italic text-[9px]">(Tanda Tangan)</div>
                    @endif
                </div>
                <p class="font-black text-zinc-900 underline uppercase">
                    {{ $bendaharaPanitia?->ustadz?->nama_lengkap ?? ($pembayaran->penerima?->name ?? 'Bendahara Panitia') }}
                </p>
            </div>
        </div>
    </div>

</body>

</html>
