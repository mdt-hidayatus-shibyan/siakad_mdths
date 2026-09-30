@section('title', 'Matriks Pendidikan')
<x-app-layout>
    <!-- HEADER -->
    <div class="mb-6 md:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-20">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('kalendar-pendidikan.index') }}"
                class="m3-btn-secondary w-10 h-10 !p-0 inline-flex items-center justify-center shadow-2xs shrink-0"
                title="Kembali ke Kalender">
                <i class="bi bi-arrow-left text-base"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Matriks Pendidikan
                </h2>
                <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-0.5 uppercase tracking-wider">
                    Struktur operasional harian KBM tersinkronisasi <span
                        class="text-emerald-600 dark:text-emerald-400 font-black">Hisab MABIMS</span>
                </p>
            </div>
        </div>

        <div class="shrink-0">
            <form action="{{ route('kalendar-pendidikan.matriks') }}" method="GET" id="formFilterTp"
                class="m-0 relative group/select">
                <div
                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10 text-zinc-400 group-focus-within/select:text-primary dark:group-focus-within/select:text-primary-dark transition-colors">
                    <i class="bi bi-calendar-range text-sm"></i>
                </div>
                <select name="tahun_id" onchange="document.getElementById('formFilterTp').submit()"
                    class="m3-input-glass w-full sm:w-64 !pl-9 !pr-9 cursor-pointer appearance-none">
                    @foreach (\App\Models\TahunPelajaran::orderBy('id', 'asc')->get() as $tahun)
                        <option value="{{ $tahun->id }}" {{ $tp->id == $tahun->id ? 'selected' : '' }}>
                            {{ $tahun->nama_hijriyah }} H | {{ $tahun->nama_masehi }} M
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 text-zinc-400">
                    <i class="bi bi-chevron-down text-xs font-bold"></i>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL MATRIKS M3 -->
    <div id="data-grid-container" class="m3-glass-card p-4 md:p-5 overflow-x-auto custom-scrollbar">
        <script>
            window.dbLiburs = @json($liburs ?? []);
            window.dbUjians = @json($ujians ?? []);
            window.dbKegiatans = @json($kegiatans ?? []);
            window.dbBulanTerSet = @json($bulanTerSet ?? []);
            window.tahunHijriiahAwal = {{ $tahunHijriiahAwal ?? 1445 }};
        </script>
        <table class="w-full min-w-[1300px] border-collapse text-center table-fixed text-xs">
            <thead>
                <tr
                    class="bg-zinc-50/90 dark:bg-zinc-950/90 text-zinc-500 dark:text-zinc-400 text-[10px] font-black uppercase tracking-wider border-b border-zinc-200/80 dark:border-zinc-800">
                    <th class="border-r border-zinc-200/80 dark:border-zinc-800 py-3.5 px-3 w-10 text-left">No
                    </th>
                    <th class="border-r border-zinc-200/80 dark:border-zinc-800 py-3.5 px-3.5 w-60 text-left">Bulan
                        Hijriyah
                    </th>
                    <th class="border-r border-zinc-200/80 dark:border-zinc-800 py-3.5 w-16">Tahun</th>
                    @for ($i = 1; $i <= 30; $i++)
                        <th class="border-r border-zinc-200/80 dark:border-zinc-800 py-3.5 w-10">
                            {{ sprintf('%02d', $i) }}
                        </th>
                    @endfor
                    <th
                        class="border-r border-zinc-200/80 dark:border-zinc-800 py-3.5 w-20 text-emerald-600 dark:text-emerald-400">
                        Efektif</th>
                    <th class="py-3.5 w-20 text-rose-600 dark:text-rose-400">Libur</th>
                </tr>
            </thead>

            <tbody id="matriksBody" class="text-xs divide-y divide-zinc-200/80 dark:divide-zinc-800/80">
                <tr>
                    <td colspan="34" class="py-16 text-center font-bold text-zinc-400">
                        <i
                            class="bi bi-arrow-repeat animate-spin text-2xl block mb-2 text-primary dark:text-primary-dark"></i>
                        Membangun Struktur Matriks Falak MABIMS...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- KETERANGAN / LEGEND M3 -->
    <div
        class="mt-5 m3-glass-card p-4 flex flex-wrap gap-x-5 gap-y-3 text-[11px] font-bold text-zinc-600 dark:text-zinc-400">
        <div class="flex items-center gap-1.5">
            <span
                class="w-4 h-4 rounded-md bg-rose-100 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 shadow-2xs"></span>
            <span>Libur Jum'at</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span
                class="w-4 h-4 flex items-center justify-center rounded-md bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-black text-[9px] shadow-2xs">X</span>
            <span>Libur Madrasah (Seharian)</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span
                class="w-4 h-4 flex items-center justify-center rounded-md bg-amber-100/90 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-black text-[8px] border border-amber-300/60 dark:border-amber-700/60 shadow-2xs">BK</span>
            <span>Bebas KBM (Sebagian Jam)</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span
                class="w-4 h-4 flex items-center justify-center rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-black text-[8px] shadow-2xs">IM</span>
            <span>IMDA / Ujian</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 flex items-center justify-center rounded-md font-black text-[8px] shadow-2xs"
                style="background-color: #0ea5e933; color: #0ea5e9;">EF</span>
            <span>Kegiatan Akademik (Warna Dinamis)</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span
                class="w-4 h-4 flex items-center justify-center rounded-md bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 font-black text-[9px] shadow-2xs">1</span>
            <span>Kegiatan Belajar Mengajar</span>
        </div>
    </div>


    <!-- MUAT ENGINE FALAK MABIMS -->
    <script src="{{ asset('assets/js/falak-engine.js') }}"></script>

    <script>
        // Override Engine UI bawaan Falak
        document.body.dataset.page = 'custom';
        window.setLoading = function(show, text = "") {};
        window.toast = function(message) {
            console.log(message);
        };
        window.currentLat = -7.6453;
        window.currentLng = 112.9075;

        const urutanBulan = [{
                nomor: 10,
                nama: "Syawal",
                offsetTahun: 0
            },
            {
                nomor: 11,
                nama: "Dzul Qadah",
                offsetTahun: 0
            },
            {
                nomor: 12,
                nama: "Dzul Hijjah",
                offsetTahun: 0
            },
            {
                nomor: 1,
                nama: "Muharram",
                offsetTahun: 1
            },
            {
                nomor: 2,
                nama: "Shafar",
                offsetTahun: 1
            },
            {
                nomor: 3,
                nama: "Rabiul Awal",
                offsetTahun: 1
            },
            {
                nomor: 4,
                nama: "Rabiul Tsani",
                offsetTahun: 1
            },
            {
                nomor: 5,
                nama: "Jumadal Ula",
                offsetTahun: 1
            },
            {
                nomor: 6,
                nama: "Jumadal Akhir",
                offsetTahun: 1
            },
            {
                nomor: 7,
                nama: "Rajab",
                offsetTahun: 1
            },
            {
                nomor: 8,
                nama: "Syaban",
                offsetTahun: 1
            }
        ];

        function formatTglIso(d) {
            const date = new Date(d);
            date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
            return date.toISOString().split('T')[0];
        }

        $(document).ready(async function() {
            await generateFalakMatrix();
        });

        // Trigger dari fungsi Auto-Refresh Grid (jika ada)
        $(document).on("dataGridRefreshed", async function() {
            await generateFalakMatrix();
        });

        async function generateFalakMatrix() {
            try {
                let htmlRows = '';
                let grandTotalEfektif = 0;
                let grandTotalLibur = 0;
                let hariEfektifCount = 1;

                // 🔴 PENTING: Paksa tahun menjadi angka ukur pasti (Bukan String)
                const baseTahun = parseInt(window.tahunHijriiahAwal, 10);

                if (isNaN(baseTahun)) {
                    console.error("Tahun awal belum diatur atau nilainya bukan angka!");
                    return;
                }

                for (let idx = 0; idx < urutanBulan.length; idx++) {
                    const infoBulan = urutanBulan[idx];

                    // Perhitungan matematika tahun yang aman
                    const thAktif = baseTahun + infoBulan.offsetTahun;

                    const monthData = await buildHijriMonthRows(thAktif, infoBulan.nomor);
                    const days = monthData.rows;

                    if (!days || days.length === 0) continue;

                    const firstDay = days[0].date;
                    const lastDay = days[days.length - 1].date;
                    const tglMulaiMasehi = formatTglIso(firstDay);
                    const tglSelesaiMasehi = formatTglIso(lastDay);
                    const urutanLogis = idx + 1;

                    // 🔴 PENTING: Cek Status Ter-Set Kebal Typo
                    const isTerSet = (window.dbBulanTerSet || []).some(namaDb =>
                        namaDb.toLowerCase().replace(/[^a-z]/g, '') === infoBulan.nama.toLowerCase().replace(
                            /[^a-z]/g, '')
                    );

                    let rowHtml = `
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group/row">
                            <td class="border-r border-zinc-200 dark:border-zinc-800 p-2 text-xs font-bold text-zinc-500 text-center">${urutanLogis}</td>
                            <td class="border-r border-zinc-200 dark:border-zinc-800 p-2 font-bold text-left text-zinc-800 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800/30 flex justify-between items-center group/btnbulan">
                                <span>${infoBulan.nama}</span>
                                
                                ${isTerSet 
                                    ? `
                                                <button type="button" class="opacity-0 group-hover/row:opacity-100 text-[10px] bg-zinc-200 text-zinc-500 font-bold px-3 py-1 rounded-full cursor-not-allowed">
                                                    <i class="bi bi-check2-all"></i> Ter-set
                                                </button>
                                            `
                                    : `@can('update kalendar-pendidikan')
                                                <a href="{{ route('kalendar-pendidikan.matriks.set-bulan') }}?nama_bulan=${infoBulan.nama}&urutan=${urutanLogis}&mulai=${tglMulaiMasehi}&selesai=${tglSelesaiMasehi}" 
                                                    class="action-modal opacity-0 group-hover/row:opacity-100 text-[10px] bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 font-bold px-3 py-1 rounded-full transition-colors flex items-center justify-center gap-1 shrink-0"
                                                    title="Edit Form Matriks">
                                                    <i class="bi bi-link-45deg"></i> Set
                                                </a>  
                                            @endcan()`
                                }
                            </td>
                            <td class="border-r border-zinc-200 dark:border-zinc-800 p-2 text-xs font-bold text-zinc-500 text-center">${thAktif}</td>
                    `;

                    let bulananEfektif = 0;
                    let bulananLibur = 0;

                    for (let i = 1; i <= 30; i++) {
                        if (i > days.length) {
                            rowHtml +=
                                `<td class="border-r border-zinc-200 dark:border-zinc-800 p-2 bg-zinc-50 dark:bg-zinc-800/50 cursor-not-allowed"></td>`;
                            continue;
                        }

                        const dayData = days[i - 1];
                        const dateObj = dayData.date;
                        const isoDate = formatTglIso(dateObj);
                        const isJumat = dateObj.getDay() === 5;

                        // 🔴 PENTING: Terapkan Waktu Mutlak (Timestamp) seperti di Index
                        const cellTime = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())
                            .getTime();

                        let isLiburDb = (window.dbLiburs || []).find(l => {
                            let s = new Date(l.tanggal_mulai).setHours(0, 0, 0, 0);
                            let e = new Date(l.tanggal_selesai).setHours(0, 0, 0, 0);
                            return cellTime >= s && cellTime <= e;
                        });

                        let isUjianDb = (window.dbUjians || []).find(u => {
                            let s = new Date(u.tanggal_mulai).setHours(0, 0, 0, 0);
                            let e = new Date(u.tanggal_selesai).setHours(0, 0, 0, 0);
                            return cellTime >= s && cellTime <= e;
                        });

                        let isKegiatanDb = (window.dbKegiatans || []).find(k => {
                            let s = new Date(k.tanggal_mulai).setHours(0, 0, 0, 0);
                            let e = new Date(k.tanggal_selesai).setHours(0, 0, 0, 0);
                            return cellTime >= s && cellTime <= e;
                        });

                        let bgClass = "bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800";
                        let textClass =
                            "text-zinc-700 dark:text-zinc-300 font-bold text-[11px] flex items-center justify-center";
                        let cellStyle = '';
                        let cellContent = '';
                        let cellTitle = `Tanggal: ${isoDate}`;

                        // LOGIKA PEWARNAAN M3 MATRIKS
                        if (isJumat) {
                            bgClass = "bg-rose-50 dark:bg-rose-900/20";
                            textClass = "text-rose-600 dark:text-rose-400 font-bold flex items-center justify-center";
                            cellTitle = `Libur Rutin Jumat (${isoDate})`;
                            bulananLibur++;
                            grandTotalLibur++;
                        } else if (isLiburDb) {
                            const isSebagian = isLiburDb.tipe_libur === 'Sebagian Jam';
                            if (isSebagian) {
                                const jams = Array.isArray(isLiburDb.jam_ke) ? isLiburDb.jam_ke.join(', ') : (isLiburDb
                                    .jam_ke || '');
                                bgClass = "bg-amber-100/90 dark:bg-amber-900/40";
                                textClass =
                                    "text-amber-800 dark:text-amber-300 font-black text-[10px] flex items-center justify-center";
                                cellContent = 'BK';
                                cellTitle = `Bebas KBM (${isLiburDb.keterangan}) - Jam: ${jams} [${isoDate}]`;
                                bulananEfektif++;
                                grandTotalEfektif++;
                            } else {
                                bgClass = "bg-rose-100 dark:bg-rose-900/40";
                                textClass =
                                    "text-rose-700 dark:text-rose-300 font-bold text-[11px] flex items-center justify-center";
                                cellContent = 'X';
                                cellTitle = `Hari Libur (${isLiburDb.keterangan}) [${isoDate}]`;
                                bulananLibur++;
                                grandTotalLibur++;
                            }
                        } else if (isUjianDb) {
                            const namaUjianLower = isUjianDb.nama_ujian.toLowerCase();
                            let kodeUjian = namaUjianLower.includes('ikhtibar') ? 'IK' : 'IM';
                            bgClass = "bg-amber-50 dark:bg-amber-900/20";
                            textClass =
                                "text-amber-700 dark:text-amber-500 font-bold text-[11px] flex items-center justify-center";
                            cellContent = kodeUjian;
                            cellTitle = `Ujian: ${isUjianDb.nama_ujian} [${isoDate}]`;
                            bulananEfektif++;
                            grandTotalEfektif++;
                        } else if (isKegiatanDb) {
                            let hex = isKegiatanDb.hex_color ||
                                (isKegiatanDb.kategori_kegiatan && isKegiatanDb.kategori_kegiatan.kode_warna) ||
                                '#3b82f6';

                            bgClass = "hover:brightness-95 dark:hover:brightness-110";
                            textClass = "font-bold text-[10px] flex items-center justify-center";
                            cellStyle = `background-color: ${hex}26; color: ${hex};`;
                            cellContent = 'EF';
                            cellTitle = `Kegiatan: ${isKegiatanDb.nama_kegiatan} [${isoDate}]`;
                            bulananEfektif++;
                            grandTotalEfektif++;
                        } else {
                            cellContent = hariEfektifCount++;
                            cellTitle = `Hari Efektif KBM #${cellContent} [${isoDate}]`;
                            bulananEfektif++;
                            grandTotalEfektif++;
                        }

                        rowHtml += `
                            <td class="border-r border-zinc-200 dark:border-zinc-800 p-0 transition-all ${bgClass} relative" style="${cellStyle}">
                                @can('update kalendar-pendidikan')
                                    <a href="{{ route('kalendar-pendidikan.matriks.create-agenda') }}?tanggal=${isoDate}" 
                                    class="action-modal w-full h-full p-2 cursor-pointer ${textClass}"
                                    title="${cellTitle}">
                                        ${cellContent}
                                    </a>
                                @else
                                    <div class="w-full h-full p-2 ${textClass}" title="${cellTitle}">
                                        ${cellContent}
                                    </div>
                                @endcan
                            </td>
                        `;
                    }

                    rowHtml += `
                            <td class="border-r border-zinc-200 dark:border-zinc-800 p-2 font-bold text-center text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/10">${bulananEfektif}</td>
                            <td class="p-2 font-bold text-center text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10">${bulananLibur}</td>
                        </tr>`;

                    htmlRows += rowHtml;
                }

                htmlRows += `
                    <tr class="bg-zinc-50 dark:bg-zinc-800/50 font-bold text-zinc-800 dark:text-zinc-200 border-t border-zinc-200 dark:border-zinc-800">
                        <td colspan="33" class="border-r border-zinc-200 dark:border-zinc-800 p-4 text-right uppercase tracking-widest text-[11px]">
                            Total Akumulasi Hari Operasional :
                        </td>
                        <td class="border-r border-zinc-200 dark:border-zinc-800 p-4 text-center text-sm text-emerald-600 dark:text-emerald-400">
                            ${grandTotalEfektif}
                        </td>
                        <td class="p-4 text-center text-sm text-rose-600 dark:text-rose-400">
                            ${grandTotalLibur}
                        </td>
                    </tr>
                `;

                $('#matriksBody').html(htmlRows);

            } catch (e) {
                console.error("Falak Engine Error: ", e);
                $('#matriksBody').html(
                    `<tr><td colspan="35" class="py-16 text-center font-bold text-rose-500 bg-rose-50 dark:bg-rose-900/10">
                        <i class="bi bi-exclamation-triangle-fill text-3xl block mb-3"></i>
                        Gagal memuat Kalender MABIMS. Silakan periksa kembali konfigurasi atau parameter tahun.
                    </td></tr>`
                );
            }
        }
    </script>
</x-app-layout>
