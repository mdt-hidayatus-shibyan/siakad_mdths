<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPJ_Keuangan_Panitia_IMNI_{{ $selectedTahun->nama_masehi }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            font-size: 10px;
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
    <div class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('pengeluaran-imni.index', ['tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Buku Kas</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak LPJ Kas (Ctrl+P)</span>
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
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H ({{ $selectedTahun->nama_masehi ?? '-' }} M)
                </p>
                <p class="text-[9px] font-medium text-zinc-500">
                    {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                </p>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center mb-5">
            <h2 class="text-xs md:text-sm font-black uppercase tracking-wider underline">
                LAPORAN PERTANGGUNGJAWABAN (LPJ) KEUANGAN IMNI
            </h2>
            <p class="text-[10px] font-bold text-zinc-500 mt-0.5">
                Tahun Pelajaran {{ $selectedTahun->nama_hijriyah ?? '-' }} H / {{ $selectedTahun->nama_masehi ?? '-' }} M
            </p>
        </div>

        <!-- BAGIAN A: REKAPITULASI PENERIMAAN KAS -->
        <div class="mb-4">
            <h3 class="text-[11px] font-black uppercase tracking-wide text-emerald-800 mb-1.5 flex items-center gap-1.5">
                <span>A. REKAPITULASI PENERIMAAN / PEMASUKAN KAS IMNI</span>
            </h3>
            <table class="w-full border-collapse border border-zinc-900 text-[10px] mb-2">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                        <th class="border border-zinc-900 py-1.5 px-2 w-8">No</th>
                        <th class="border border-zinc-900 py-1.5 px-3 text-left">Jenjang / Tingkat Murid</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-24">Jumlah Murid</th>
                        <th class="border border-zinc-900 py-1.5 px-3 w-28 text-right">Target Tagihan</th>
                        <th class="border border-zinc-900 py-1.5 px-3 w-28 text-right">Realisasi Terbayar</th>
                        <th class="border border-zinc-900 py-1.5 px-3 w-28 text-right">Sisa Piutang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemasukanPerTingkat as $idx => $row)
                        <tr>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="border border-zinc-900 py-1.5 px-3 font-bold uppercase">{{ $row->nama_tingkat }} ({{ $row->kode_tingkat }})</td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-semibold">{{ $row->total_peserta }} Murid</td>
                            <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono">Rp {{ number_format($row->tagihan, 0, ',', '.') }}</td>
                            <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono font-bold text-emerald-800">Rp {{ number_format($row->bayar, 0, ',', '.') }}</td>
                            <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono {{ $row->sisa > 0 ? 'text-amber-800' : '' }}">Rp {{ number_format($row->sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-zinc-900 py-3 text-center text-zinc-400 font-bold">
                                Belum ada rincian penerimaan per tingkat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-zinc-100 font-black">
                        <td colspan="2" class="border border-zinc-900 py-1.5 px-3 uppercase text-right">Total Pemasukan Kas (A):</td>
                        <td class="border border-zinc-900 py-1.5 px-2 text-center font-bold">{{ $countPeserta }} Murid</td>
                        <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono text-emerald-800">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                        <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono text-amber-800">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BAGIAN B: REKAPITULASI PENGELUARAN KAS PER POS -->
        <div class="mb-4">
            <h3 class="text-[11px] font-black uppercase tracking-wide text-amber-800 mb-1.5 flex items-center gap-1.5">
                <span>B. REKAPITULASI PENGELUARAN KAS PER POS KATEGORI</span>
            </h3>
            <table class="w-full border-collapse border border-zinc-900 text-[10px] mb-2">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                        <th class="border border-zinc-900 py-1.5 px-2 w-8">No</th>
                        <th class="border border-zinc-900 py-1.5 px-3 text-left">Pos Kategori Pengeluaran</th>
                        <th class="border border-zinc-900 py-1.5 px-2 w-28">Banyak Transaksi</th>
                        <th class="border border-zinc-900 py-1.5 px-3 w-36 text-right">Total Realisasi (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noKat = 1; @endphp
                    @forelse ($rekapKategori as $kat)
                        <tr>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-bold">{{ $noKat++ }}</td>
                            <td class="border border-zinc-900 py-1.5 px-3 font-bold">{{ $kat->kategori }}</td>
                            <td class="border border-zinc-900 py-1.5 px-2 text-center font-semibold">{{ $kat->count }} kali</td>
                            <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono font-bold text-amber-800">
                                Rp {{ number_format($kat->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border border-zinc-900 py-3 text-center text-zinc-400 font-bold">
                                Belum ada pos pengeluaran yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-zinc-100 font-black">
                        <td colspan="2" class="border border-zinc-900 py-1.5 px-3 uppercase text-right">Total Pengeluaran Kas (B):</td>
                        <td class="border border-zinc-900 py-1.5 px-2 text-center">{{ $pengeluarans->count() }} transaksi</td>
                        <td class="border border-zinc-900 py-1.5 px-3 text-right font-mono text-amber-800">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BAGIAN C: RINCIAN DAFTAR TRANSAKSI PENGELUARAN -->
        <div class="mb-4">
            <h3 class="text-[11px] font-black uppercase tracking-wide text-zinc-800 mb-1.5 flex items-center gap-1.5">
                <span>C. RINCIAN BUKU KAS KELUAR OPERASIONAL IMNI</span>
            </h3>
            <table class="w-full border-collapse border border-zinc-900 text-[9px] mb-2">
                <thead>
                    <tr class="bg-zinc-100 text-zinc-900 font-black text-center">
                        <th class="border border-zinc-900 py-1 px-1 w-6">No</th>
                        <th class="border border-zinc-900 py-1 px-2 w-16">Tanggal</th>
                        <th class="border border-zinc-900 py-1 px-2 w-24">Kode</th>
                        <th class="border border-zinc-900 py-1 px-2 w-24">Pos Kategori</th>
                        <th class="border border-zinc-900 py-1 px-2 text-left">Uraian / Keperluan</th>
                        <th class="border border-zinc-900 py-1 px-2 w-24">Penerima Dana</th>
                        <th class="border border-zinc-900 py-1 px-2 w-24 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengeluarans as $idx => $p)
                        <tr class="hover:bg-zinc-50">
                            <td class="border border-zinc-900 py-1 px-1 text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="border border-zinc-900 py-1 px-2 text-center">{{ $p->tanggal_pengeluaran ? $p->tanggal_pengeluaran->format('d/m/Y') : '-' }}</td>
                            <td class="border border-zinc-900 py-1 px-2 text-center font-mono font-bold">{{ $p->kode_transaksi }}</td>
                            <td class="border border-zinc-900 py-1 px-2 text-center font-semibold">{{ $p->kategori }}</td>
                            <td class="border border-zinc-900 py-1 px-2 font-medium">{{ $p->judul_pengeluaran }}</td>
                            <td class="border border-zinc-900 py-1 px-2 text-center">{{ $p->penerima_dana ?? '-' }}</td>
                            <td class="border border-zinc-900 py-1 px-2 text-right font-mono font-bold">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-zinc-900 py-4 text-center text-zinc-400 font-bold">
                                Belum ada rincian item transaksi keluar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- BAGIAN D: NERACA SALDO AKHIR KAS -->
        <div class="mb-5 p-3 bg-zinc-50 border-2 border-zinc-900 rounded-xl">
            <h3 class="text-[11px] font-black uppercase tracking-wide text-zinc-900 mb-2">
                D. NERACA SALDO AKHIR KAS PANITIA IMNI
            </h3>
            <div class="grid grid-cols-3 gap-3 text-xs mb-2">
                <div class="p-2 bg-white rounded-lg border border-zinc-300">
                    <p class="text-[10px] text-zinc-500 font-bold uppercase">Total Pemasukan Kas (A)</p>
                    <p class="text-sm font-black font-mono text-emerald-800 mt-0.5">
                        Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded-lg border border-zinc-300">
                    <p class="text-[10px] text-zinc-500 font-bold uppercase">Total Pengeluaran Kas (B)</p>
                    <p class="text-sm font-black font-mono text-amber-800 mt-0.5">
                        Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-2 bg-white rounded-lg border-2 {{ $saldoAkhir >= 0 ? 'border-emerald-600 bg-emerald-50/50' : 'border-rose-600 bg-rose-50/50' }}">
                    <p class="text-[10px] font-bold uppercase {{ $saldoAkhir >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                        Saldo Kas Bersih (A - B)
                    </p>
                    <p class="text-sm font-black font-mono {{ $saldoAkhir >= 0 ? 'text-emerald-900' : 'text-rose-900' }} mt-0.5">
                        Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="text-[10px] font-semibold text-zinc-700 italic">
                Terbilang Saldo: <span class="font-bold uppercase text-zinc-900"># {{ ucwords(terbilang($saldoAkhir)) }} Rupiah #</span>
            </div>
        </div>

        <!-- BAGIAN E: PENGESAHAN & TANDA TANGAN -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-6 break-inside-avoid">
            <div>
                <p class="text-zinc-500 text-[10.5px]">Mengetahui,</p>
                <p class="font-bold text-zinc-800 text-xs">Ketua Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])
                        ) !!}
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">(Tanda Tangan & Stempel)</div>
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
                <p class="text-zinc-500 text-[10.5px]">{{ getSetting('kota_madrasah', 'Bangkalan') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-zinc-800 text-xs">Bendahara Panitia IMNI</p>
                <div class="min-h-[65px] flex items-center justify-center my-1.5">
                    @if (!empty($bendaharaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz_id])
                        ) !!}
                    @elseif (!empty($bendaharaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->margin(0)->generate(
                            URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $bendaharaPanitia->ustadz->id])
                        ) !!}
                    @else
                        <div class="h-14 flex items-center justify-center text-zinc-300 italic text-[10px]">(Tanda Tangan)</div>
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
