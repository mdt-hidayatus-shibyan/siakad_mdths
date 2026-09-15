<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar_Mutasi_A6_{{ $tabungan->nomor_rekening }}_{{ Str::slug($tabungan->nama_nasabah) }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* Pengaturan Standar Cetak Kertas Ukuran A6 (105mm x 148mm) */
        @page {
            size: A6 portrait;
            margin: 4mm 5mm 4mm 5mm;
        }

        @media print {
            body {
                background-color: #fff;
                color: #000;
                font-size: 8.5px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            html,
            body {
                height: 99%;
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            background: #fff;
            padding: 4px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #334155;
            padding: 2.5px 3.5px;
            font-size: 8px;
        }

        .border-none td,
        .border-none th {
            border: none !important;
            padding: 1.5px 2px;
        }
    </style>
</head>

<body class="bg-white">

    <!-- TOMBOL CETAK BROWSER (NO-PRINT) -->
    <div class="print:hidden flex justify-center items-center py-2 bg-slate-100 gap-3 border-b mb-3 no-print">
        <div
            class="px-3 py-1 bg-amber-100 text-amber-800 border border-amber-300 rounded text-[11px] font-bold flex items-center shadow-xs">
            <i class="bi bi-file-earmark-text-fill mr-1.5 text-amber-600"></i> FORMAT CETAK A6 (PENGGANTI BUKU)
        </div>
        <button onclick="window.print()"
            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
            <i class="bi bi-printer-fill"></i> Cetak Lembar A6
        </button>
    </div>

    <!-- KOP SURAT (MENYESUAIKAN HEADER CETAK RAPOR ARSIP) -->
    <div class="flex justify-between items-center border-b-[2px] border-double border-slate-700 pb-2 mb-2">
        <div class="flex items-center gap-2">
            @if (getSetting('kop_logo'))
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Logo Madrasah"
                    class="w-[110px] max-h-[38px] object-contain">
            @else
                <div class="font-black text-[10px] text-slate-900 leading-tight">
                    MDT HIDAYATUS SHIBYAN
                </div>
            @endif
        </div>
        <div class="text-right leading-tight">
            <h1 class="m-0 text-[10px] font-black text-blue-700 uppercase tracking-wide">
                LEMBAR MUTASI TABUNGAN
            </h1>
            <p class="m-0 text-[7.5px] font-bold text-slate-600 uppercase">
                Pengganti Buku Fisik (Ukuran A6)
            </p>
            <p class="mt-0.5 text-[7px] font-mono text-slate-500">
                NO. REK: <strong>{{ $tabungan->nomor_rekening }}</strong> &bull; {{ date('d/m/Y') }}
            </p>
        </div>
    </div>

    <!-- DATA IDENTITAS NASABAH -->
    <table class="border-none w-full text-[8px] mb-2">
        <tr class="border-none">
            <td class="font-bold w-16 text-slate-600 uppercase text-[7.5px] align-top">Nasabah</td>
            <td class="align-top w-1">:</td>
            <td class="font-black uppercase text-slate-900 text-[8.5px] align-top truncate">
                {{ $tabungan->nama_nasabah }}</td>

            <td class="font-bold w-14 text-slate-600 uppercase text-[7.5px] align-top pl-2">Kelas/Ruang</td>
            <td class="align-top w-1">:</td>
            <td class="font-bold text-slate-800 align-top truncate">
                {{ $tabungan->murid?->ruangans?->first()?->nama_ruangan ?? ($tabungan->ruangan?->nama_ruangan ?? $tabungan->jenis_nasabah) }}
            </td>
        </tr>
        <tr class="border-none">
            <td class="font-bold text-slate-600 uppercase text-[7.5px] align-top">NISM/ID</td>
            <td class="align-top">:</td>
            <td class="font-mono font-bold text-slate-700 align-top">{{ $tabungan->identitas_nasabah }}</td>

            <td class="font-bold text-slate-600 uppercase text-[7.5px] align-top pl-2">Periode</td>
            <td class="align-top">:</td>
            <td class="font-bold text-slate-700 align-top truncate">
                {{ $tabungan->periodeTabungan?->nama_periode ?? 'Reguler' }}</td>
        </tr>
    </table>

    <!-- TABEL REKAPITULASI MUTASI PER BULAN -->
    <div class="text-[7.5px] font-black uppercase tracking-wider text-slate-700 mb-1 flex items-center justify-between">
        <span>1. Rincian Mutasi Per Bulan</span>
        <span class="font-normal text-[7px] text-slate-500">{{ count($mutasi['rekap_bulanan']) }} Bulan</span>
    </div>

    <table class="report-table mb-2">
        <thead>
            <tr class="bg-slate-100 text-center font-bold text-[7.5px]">
                <th class="w-5 py-1">No</th>
                <th class="text-left py-1">Bulan Masehi</th>
                <th class="w-14 text-right py-1">Setoran</th>
                <th class="w-14 text-right py-1">Penarikan</th>
                <th class="w-16 text-right py-1">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mutasi['rekap_bulanan'] as $b)
                <tr>
                    <td class="text-center font-bold">{{ $loop->iteration }}</td>
                    <td class="font-semibold truncate">{{ $b['label'] }}</td>
                    <td class="text-right font-mono font-bold text-emerald-700">
                        {{ $b['total_setor'] > 0 ? number_format($b['total_setor'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold text-rose-600">
                        {{ $b['total_tarik'] > 0 ? number_format($b['total_tarik'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-mono font-black text-slate-900 bg-slate-50">
                        Rp {{ number_format($b['saldo_akhir'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-2 text-slate-400">Belum ada catatan mutasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- RINGKASAN FINANSIAL & HAK BERSIH AKHIR -->
    <div class="border border-slate-700 rounded p-1.5 bg-slate-50/80 mb-2 space-y-0.5 text-[8px]">
        <div class="flex justify-between items-center text-slate-600">
            <span>Total Setoran Terkumpul:</span>
            <span class="font-mono font-bold text-slate-900">Rp
                {{ number_format($tabungan->total_setor, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between items-center text-slate-600">
            <span>Total Penarikan Terjadi:</span>
            <span class="font-mono font-bold text-rose-600">Rp
                {{ number_format($tabungan->total_tarik, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between items-center text-slate-600 pt-0.5 border-t border-slate-300">
            <span>Saldo Tercatat di Sistem:</span>
            <span class="font-mono font-bold text-slate-900">Rp
                {{ number_format($tabungan->saldo, 0, ',', '.') }}</span>
        </div>
        @if ($kalkulasi)
            <div class="flex justify-between items-center text-amber-700 pt-0.5 border-t border-slate-300">
                <span>Potongan Madrasah ({{ $kalkulasi['persentase_potongan'] }}%):</span>
                <span class="font-mono font-bold">- Rp
                    {{ number_format($kalkulasi['nominal_potongan'], 0, ',', '.') }}</span>
            </div>
            <div
                class="flex justify-between items-center bg-emerald-100 px-1 py-0.5 rounded border border-emerald-300 font-black text-emerald-900 text-[8.5px] mt-1">
                <span>HAK BERSIH DITERIMA:</span>
                <span class="font-mono text-[9.5px]">Rp
                    {{ number_format($kalkulasi['saldo_bersih_total'], 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <!-- AREA TANDA TANGAN (KOMPAK UNTUK A6) -->
    <div class="flex justify-between text-center text-[7.5px] leading-tight pt-1">
        <div class="w-[45%]">
            <div>Wali Murid / Nasabah,</div>
            <div class="h-8"></div>
            <div class="font-bold border-t border-dotted border-slate-600 pt-0.5 truncate">
                {{ $tabungan->nama_nasabah }}
            </div>
        </div>
        <div class="w-[45%]">
            <div>Somorkoneng, {{ date('d/m/Y') }}<br>Petugas Tabungan,</div>
            <div class="h-8"></div>
            <div class="font-bold border-t border-dotted border-slate-600 pt-0.5 truncate">
                {{ $tabungan->diverifikasiOleh?->name ?? (Auth::user()->name ?? 'Petugas Tabungan') }}
            </div>
        </div>
    </div>

</body>

</html>
