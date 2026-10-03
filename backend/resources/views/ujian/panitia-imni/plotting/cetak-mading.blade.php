<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mading_Plotting_IMNI_Hari_{{ $hariKe }}_{{ $tanggal }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.6cm 0.7cm;
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

            tr,
            .room-box {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.3;
        }

        .sheet-container {
            background: white;
            max-width: 21cm;
            margin: 5mm auto 15mm auto;
            padding: 0.7cm 0.9cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!-- FLOATING ACTION BAR -->
    <div
        class="no-print fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-zinc-200">
        <a href="{{ route('plotting-imni.harian', ['tanggal' => $tanggal, 'tahun_id' => $selectedTahun->id]) }}"
            class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold transition-all flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button type="button" onclick="window.print()"
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-lg transition-all flex items-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Mading (Ctrl+P)</span>
        </button>
    </div>

    <div class="sheet-container">
        <!-- KOP SURAT RESMI -->
        <div class="flex justify-between items-center border-b-[2.5px] border-double border-slate-800 pb-2 mb-2.5">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png'))) }}"
                    alt="Logo Madrasah" class="h-[65px] max-h-[75px] w-auto object-contain">
            </div>
            <div class="text-right leading-tight">
                <h1 class="m-0 text-xs md:text-sm font-black text-indigo-700 underline uppercase tracking-wide">
                    PANITIA IMTIHAN NIHA'I (IMNI)
                </h1>
                <h2 class="m-0 text-[11px] md:text-xs font-extrabold uppercase tracking-wide text-zinc-900">
                    {{ getSetting('nama_madrasah', 'MDT HIDAYATUS SHIBYAN') }}
                </h2>
                <p class="mt-0.5 text-[9px] font-bold text-zinc-600 uppercase">
                    Tahun Pelajaran {{ $selectedTahun->nama_hijriyah }} H ({{ $selectedTahun->nama_masehi }} M)
                </p>
                <p class="text-[8.5px] font-medium text-zinc-500">
                    {{ getSetting('alamat_madrasah', 'Dsn. Morkoneng Desa Somorkoneng Kec. Kwanyar Kab. Bangkalan') }}
                </p>
            </div>
        </div>

        <!-- JUDUL DOKUMEN PENGUMUMAN MADING -->
        <div class="text-center mb-2.5">
            <h2 class="text-xs md:text-[13px] font-black uppercase tracking-wider underline">
                PENGUMUMAN DENAH DUDUK & PEMBAGIAN RUANGAN UJIAN IMNI
            </h2>
            <div class="text-[10px] font-black text-indigo-900 mt-0.5 uppercase tracking-wide">
                HARI KE-{{ $hariKe }} — {{ $namaHari }}, {{ $tanggalFormat }}
            </div>
        </div>

        <!-- INFO JADWAL MAPEL HARI INI -->
        <div class="grid grid-cols-2 gap-2 mb-3 text-[9.5px] bg-indigo-50/60 p-2 rounded-lg border border-indigo-200">
            <div>
                <span class="font-black text-orange-700 uppercase tracking-wider block mb-0.5">
                    Mata Pelajaran Kelas 6 IBT:
                </span>
                @if ($jadwalIbt->count() > 0)
                    <div class="font-bold text-zinc-800 space-y-0.5">
                        @foreach ($jadwalIbt as $jb)
                            <div>• {{ $jb->mataPelajaran?->nama_mapel ?? $jb->nama_mata_pelajaran_custom }} <span
                                    class="font-mono text-[9px] text-zinc-500">({{ $jb->waktu_mulai ? substr($jb->waktu_mulai, 0, 5) : '' }}
                                    - {{ $jb->waktu_selesai ? substr($jb->waktu_selesai, 0, 5) : '' }} WIB)</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="italic text-zinc-400">- Tidak ada jadwal ujian -</span>
                @endif
            </div>

            <div>
                <span class="font-black text-blue-700 uppercase tracking-wider block mb-0.5">
                    Mata Pelajaran Kelas 3 TSA:
                </span>
                @if ($jadwalTsa->count() > 0)
                    <div class="font-bold text-zinc-800 space-y-0.5">
                        @foreach ($jadwalTsa as $jt)
                            <div>• {{ $jt->mataPelajaran?->nama_mapel ?? $jt->nama_mata_pelajaran_custom }} <span
                                    class="font-mono text-[9px] text-zinc-500">({{ $jt->waktu_mulai ? substr($jt->waktu_mulai, 0, 5) : '' }}
                                    - {{ $jt->waktu_selesai ? substr($jt->waktu_selesai, 0, 5) : '' }} WIB)</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="italic text-zinc-400">- Tidak ada jadwal ujian -</span>
                @endif
            </div>
        </div>

        <!-- GRID PEMBAGIAN RUANGAN PESERTA -->
        <div class="grid {{ $daftarRuanganImni->count() > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-3 mb-3">
            @foreach ($daftarRuanganImni as $rg)
                @php
                    $listPeserta = $pesertaPerRuangan->get($rg->id) ?? collect();
                    $pw = $pengawasMap->get($rg->id);
                @endphp
                <div class="room-box border border-zinc-800 rounded-lg overflow-hidden flex flex-col">
                    <!-- Room Header -->
                    <div class="bg-zinc-900 text-white p-1.5 px-2.5 flex items-center justify-between text-[10px]">
                        <div class="font-black tracking-wide">
                            {{ $rg->nama_ruangan_imni ?: $rg->nama_ruangan }}
                            <span
                                class="font-normal text-zinc-300 text-[9px]">({{ $rg->ruanganFisik?->nama_ruangan ?? 'Ruangan Fisik' }})</span>
                        </div>
                        <div class="text-[9px] font-bold text-zinc-200">
                            Pengawas: -
                        </div>
                    </div>

                    <!-- Room Table -->
                    <table class="w-full text-left text-[9px] border-collapse">
                        <thead>
                            <tr class="bg-zinc-100 border-b border-zinc-400 text-zinc-700 font-black">
                                <th class="py-1 px-1.5 text-center w-8 border-r border-zinc-300">Meja</th>
                                <th class="py-1 px-1.5 w-20 border-r border-zinc-300">NISM</th>
                                <th class="py-1 px-1.5 border-r border-zinc-300">Nama Peserta</th>
                                <th class="py-1 px-1.5 text-center w-14">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($listPeserta as $p)
                                @php
                                    $ruangAsal =
                                        $p->pesertaImni?->ruanganAsal?->nama_ruangan ??
                                        ($p->pesertaImni?->level?->nama_level ??
                                            ($p->pesertaImni?->tingkat_id == 3
                                                ? '3 TSA'
                                                : ($p->pesertaImni?->tingkat_id == 2
                                                    ? '6 IBT'
                                                    : '-')));
                                    $isTsa =
                                        str_contains(strtoupper($ruangAsal), 'TSA') ||
                                        $p->pesertaImni?->tingkat_id == 3;
                                @endphp
                                <tr class="{{ $loop->even ? 'bg-zinc-50/50' : 'bg-white' }}">
                                    <td
                                        class="py-0.5 px-1.5 text-center font-mono font-black text-indigo-700 border-r border-zinc-200">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td
                                        class="py-0.5 px-1.5 font-mono font-bold text-zinc-600 border-r border-zinc-200 text-[8.5px]">
                                        {{ $p->pesertaImni?->murid?->nism ?? '-' }}
                                    </td>
                                    <td
                                        class="py-0.5 px-1.5 font-bold text-zinc-900 border-r border-zinc-200 truncate max-w-[120px]">
                                        {{ $p->pesertaImni?->murid?->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="py-0.5 px-1.5 text-center font-black">
                                        <span
                                            class="{{ $isTsa ? 'text-blue-700' : 'text-orange-700' }}">{{ $ruangAsal }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-3 text-center text-zinc-400 italic">
                                        Belum ada data peserta.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <!-- TANDA TANGAN & QR CODE KETUA PANITIA IMNI -->
        <div class="flex justify-end text-center text-[10px] mt-2 page-break-inside-avoid">
            <div class="w-56">
                <p class="text-zinc-600 text-[9.5px]">Bangkalan, {{ $tanggalFormat }}</p>
                <p class="font-bold text-zinc-800 text-[10px]">Ketua Panitia IMNI,</p>

                <div class="min-h-[55px] flex items-center justify-center my-1">
                    @if (!empty($ketuaPanitia?->ustadz_id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz_id])) !!}
                    @elseif (!empty($ketuaPanitia?->ustadz?->id))
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(URL::signedRoute('profil.publik', ['tipe' => 'ustadz', 'id' => $ketuaPanitia->ustadz->id])) !!}
                    @else
                        <div class="h-12 flex items-center justify-center text-zinc-300 italic text-[9px]">
                            (Tanda Tangan & Stempel)
                        </div>
                    @endif
                </div>

                <p class="font-black text-zinc-900 underline uppercase text-[10px]">
                    {{ $ketuaPanitia?->ustadz?->nama_lengkap ?? 'Ketua Panitia IMNI' }}
                </p>
                @if ($ketuaPanitia?->ustadz?->nip_ustadz)
                    <p class="text-[8.5px] text-zinc-500 font-mono">NIP: {{ $ketuaPanitia->ustadz->nip_ustadz }}</p>
                @endif
            </div>
        </div>
    </div>

</body>

</html>
