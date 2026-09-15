<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Ujian_Alquran_{{ Str::slug($ujian->nama_ujian) }}_{{ $ujian->tahunPelajaran->nama_masehi ?? 'TP' }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm 1cm;
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

            .rekap-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            tr {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }

        .rekap-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto;
            padding: 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-left img {
            max-height: 75px;
            width: auto;
            object-fit: contain;
        }

        .kop-right {
            text-align: right;
            line-height: 1.35;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .table-data th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .table-stat {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px 0;
            font-size: 10.5px;
        }

        .table-stat th,
        .table-stat td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            text-align: center;
        }

        .table-stat th {
            background-color: #f8fafc;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <!-- TOMBOL AKSI ATAS -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-3 px-4 bg-slate-900 text-white gap-3 border-b mb-6 shadow-md">
        <div
            class="px-3 py-1.5 bg-blue-500/20 text-blue-300 border border-blue-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            📊 BERITA ACARA & REKAPITULASI UJIAN AL-QUR'AN
        </div>
        <button onclick="window.print()"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95 cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold transition-all shadow-md cursor-pointer">
            ✕ Tutup
        </button>
    </div>

    <div class="rekap-container">

        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="kop-left">
                <img src="{{ asset(getSetting('kop_logo', 'assets/LOGO MDT.png')) }}" alt="Logo Madrasah">
            </div>
            <div class="kop-right">
                <h1 class="text-sm font-black uppercase text-slate-900">MADRASAH DINIYAH TAKMILIYAH HIDAYATUS SHIBYAN
                </h1>
                <p class="text-xs text-slate-600 font-semibold">Dsn. Morkoneng Desa Somorkoneng Kwanyar Bangkalan</p>
                <p class="text-xs text-slate-700">Tahun Pelajaran:
                    <strong>{{ $ujian->tahunPelajaran->nama_masehi ?? '-' }} M /
                        {{ $ujian->tahunPelajaran->nama_hijriyah ?? '-' }} H</strong>
                </p>
            </div>
        </div>

        <!-- JUDUL -->
        <div class="text-center my-3">
            <h2 class="text-xs md:text-sm font-black uppercase underline tracking-wider">BERITA ACARA & REKAPITULASI
                NILAI UJIAN
                AL-QUR'AN</h2>
            <p class="text-[10px] font-bold text-slate-700 mt-0.5">SYARAT KELULUSAN TINGKAT IBTIDAIYAH</p>
        </div>

        <!-- INFORMASI EVENT -->
        <table class="w-full text-[10px] mb-3 leading-relaxed">
            <tr>
                <td class="w-28 font-bold">Nama Agenda Ujian</td>
                <td class="w-3">:</td>
                <td class="font-semibold">{{ $ujian->nama_ujian }}</td>
                <td class="w-24 font-bold">Hari / Tanggal</td>
                <td class="w-3">:</td>
                <td>{{ \Carbon\Carbon::parse($ujian->tanggal_ujian ?? ($ujian->tanggal_pelaksanaan ?? now()))->translatedFormat('l, d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="font-bold">Standar Kelulusan</td>
                <td>:</td>
                <td>Nilai Akhir &ge; {{ (int) $ujian->kkm_kelulusan }} (Lulus) | &lt; {{ (int) $ujian->kkm_kelulusan }}
                    (Tidak Lulus)</td>
                <td class="font-bold">Ruangan</td>
                <td>:</td>
                <td>{{ $ruanganAktif ? $ruanganAktif->nama_ruangan . ' (' . ($ruanganAktif->level->nama_level ?? '') . ')' : 'Semua Ruangan' }}
                </td>
            </tr>
            <tr>
                <td class="font-bold">Dewan Penguji</td>
                <td>:</td>
                <td colspan="4">
                    @forelse($ujian->juris as $j)
                        {{ $j->ustadz->nama_lengkap ?? '-' }}
                        <em>({{ $j->kategori_juri ?? ($j->peran_juri ?? 'Penguji') }}{{ $j->is_penanggung_jawab ? ' - PJ' : '' }})</em>{{ !$loop->last ? ', ' : '' }}
                    @empty
                        -
                    @endforelse
                </td>
            </tr>
        </table>

        <!-- STATISTIK RINGKAS -->
        @php
            $total = $pesertas->count();
            $lulus = $pesertas->where('status_kelulusan', 'Lulus')->count();
            $tidakLulus = $pesertas->where('status_kelulusan', 'Tidak Lulus')->count();
            $belum = $pesertas->where('status_kelulusan', 'Belum Diuji')->count();
            $persenLulus = $total > 0 ? round(($lulus / $total) * 100, 1) : 0;
        @endphp
        <table class="table-stat">
            <thead>
                <tr>
                    <th>Total Peserta Murid</th>
                    <th>Lulus (Nilai &ge; {{ (int) $ujian->kkm_kelulusan }})</th>
                    <th>Tidak Lulus / Remidi</th>
                    <th>Belum Diuji</th>
                    <th>Persentase Kelulusan</th>
                </tr>
            </thead>
            <tbody>
                <tr class="font-bold">
                    <td>{{ $total }} Murid</td>
                    <td class="text-emerald-700 font-extrabold">{{ $lulus }} Murid</td>
                    <td class="text-rose-700 font-extrabold">{{ $tidakLulus }} Murid</td>
                    <td class="text-amber-700">{{ $belum }} Murid</td>
                    <td class="text-indigo-700 font-extrabold">{{ $persenLulus }}%</td>
                </tr>
            </tbody>
        </table>

        <!-- TABEL REKAPITULASI PESERTA -->
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 75px;">No. Peserta</th>
                    <th>Nama Lengkap Murid</th>
                    <th style="width: 75px;">NISM</th>
                    <th style="width: 65px;">Ruangan</th>
                    <th style="width: 45px;">Jali<br><span
                            class="text-[8px] font-normal">(-{{ (int) $ujian->bobot_jali }})</span></th>
                    <th style="width: 45px;">Khofi<br><span
                            class="text-[8px] font-normal">(-{{ (int) $ujian->bobot_khofi }})</span></th>
                    <th style="width: 45px;">Minus</th>
                    <th style="width: 45px;">Nilai</th>
                    <th style="width: 70px;">Predikat</th>
                    <th style="width: 65px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pesertas as $idx => $p)
                    <tr
                        class="text-center {{ $p->status_kelulusan === 'Lulus' ? 'bg-white' : ($p->status_kelulusan === 'Tidak Lulus' ? 'bg-rose-50/50' : 'bg-slate-50/30') }}">
                        <td>{{ $idx + 1 }}</td>
                        <td class="font-mono text-[9.5px] font-bold">{{ $p->nomor_peserta ?? '-' }}</td>
                        <td class="text-left font-bold uppercase">{{ $p->murid->nama_lengkap ?? '-' }}</td>
                        <td class="font-mono text-[9.5px]">{{ $p->murid->nism ?? '-' }}</td>
                        <td>{{ $p->ruangan->nama_ruangan ?? '-' }}</td>
                        <td class="text-rose-700 font-bold font-mono">{{ $p->jumlah_khoto_jali }}</td>
                        <td class="text-amber-700 font-bold font-mono">{{ $p->jumlah_khoto_khofi }}</td>
                        <td class="font-bold text-rose-800 font-mono">-{{ $p->total_pengurangan }}</td>
                        <td class="font-black text-slate-900 font-mono">{{ number_format($p->nilai_akhir, 0) }}</td>
                        <td class="text-[9px] font-semibold">
                            {{ $p->status_kelulusan === 'Lulus' ? $p->predikat : '-' }}</td>
                        <td>
                            @if ($p->status_kelulusan === 'Lulus')
                                <span class="font-bold text-emerald-700">LULUS</span>
                            @elseif($p->status_kelulusan === 'Tidak Lulus')
                                <span class="font-bold text-rose-700">TIDAK LULUS</span>
                            @else
                                <span class="text-slate-500 italic">Belum Diuji</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-slate-500 italic">Belum ada data peserta
                            terdaftar pada ujian ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- CATATAN BERITA ACARA -->
        <div class="mt-3 p-2 border border-dashed border-slate-300 rounded bg-slate-50 text-[9.5px] leading-relaxed">
            <span class="font-bold">Catatan Dewan Juri / Berita Acara:</span>
            Pelaksanaan Ujian Al-Qur'an sebagai prasyarat kelulusan tingkat Ibtidaiyah telah dilaksanakan secara tertib,
            adil, dan transparan sesuai ketentuan penilaian Khotho' Jali (-{{ (int) $ujian->bobot_jali }} pt) dan
            Khotho' Khofi (-{{ (int) $ujian->bobot_khofi }} pt). Murid yang belum memenuhi
            standar nilai kelulusan (&lt; {{ (int) $ujian->kkm_kelulusan }}) akan mengikuti ujian susulan / perbaikan
            di tahun berikutnya saat berada di Kelas 6
            Ibtidaiyah.
        </div>

        <!-- TANDA TANGAN (DEWAN PENGUJI: JURI 1 & JURI 2) -->
        <table class="w-full text-center mt-5 text-[9.5px]" style="table-layout: fixed; page-break-inside: avoid;">
            <tr>
                <!-- JURI 1 -->
                <td class="w-1/2 align-top text-center" style="padding-right: 1.5cm;">
                    <p class="mb-0">Dewan Penguji,</p>
                    <p class="font-bold">
                        @php
                            $juri1 =
                                $ujian->juris->firstWhere('peran_juri', 'Juri 1') ??
                                ($ujian->juris->firstWhere('kategori_juri', 'Khotho Jali') ?? $ujian->juris->first());
                        @endphp
                        Juri 1 ({{ $juri1->kategori_juri ?? 'Khotho\' Jali' }})
                    </p>
                    <div class="my-1.5 flex justify-center items-center" style="height: 60px;">
                        @if (!empty($juri1?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $juri1->ustadz_id]),
                            ) !!}
                        @endif
                    </div>
                    <p class="font-bold underline uppercase">
                        {{ $juri1?->ustadz?->nama_lengkap ?? 'Dewan Penguji 1' }}
                    </p>
                    @if (!empty($juri1?->ustadz?->nigm))
                        <p class="text-[9px] text-slate-500 font-mono">NIGM: {{ $juri1->ustadz->nigm }}</p>
                    @endif
                </td>

                <!-- JURI 2 -->
                <td class="w-1/2 align-top text-center" style="padding-left: 1.5cm;">
                    <p class="mb-0">Bangkalan,
                        {{ \Carbon\Carbon::parse($ujian->tanggal_ujian ?? ($ujian->tanggal_pelaksanaan ?? now()))->translatedFormat('d F Y') }}
                    </p>
                    <p class="font-bold">
                        @php
                            $juri2 =
                                $ujian->juris->firstWhere('peran_juri', 'Juri 2') ??
                                ($ujian->juris->firstWhere('kategori_juri', 'Khotho Khofi') ??
                                    $ujian->juris->skip(1)->first());
                        @endphp
                        Juri 2 ({{ $juri2->kategori_juri ?? 'Khotho\' Khofi' }})
                    </p>
                    <div class="my-1.5 flex justify-center items-center" style="height: 60px;">
                        @if (!empty($juri2?->ustadz_id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $juri2->ustadz_id]),
                            ) !!}
                        @elseif(!empty($administrator?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(60)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $administrator->id]),
                            ) !!}
                        @endif
                    </div>
                    <p class="font-bold underline uppercase">
                        {{ $juri2?->ustadz?->nama_lengkap ?? ($administrator?->nama_lengkap ?? 'Dewan Penguji 2') }}
                    </p>
                    @if (!empty($juri2?->ustadz?->nigm))
                        <p class="text-[9px] text-slate-500 font-mono">NIGM: {{ $juri2->ustadz->nigm }}</p>
                    @endif
                </td>
            </tr>
        </table>

    </div>

    @if (request()->has('print') || request()->has('auto_print') || request()->has('download'))
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
