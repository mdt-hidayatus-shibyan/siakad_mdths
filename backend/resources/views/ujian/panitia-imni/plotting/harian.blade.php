@section('title', "Plotting Peserta & Pengawas - Hari Ke-{$hariKe} ({$tanggalFormat})")

<x-app-layout>
    <div class="space-y-6">
        <!-- 1. HEADER & ACTION TOOLBAR -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 print:hidden">
            <div>

                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                    Plotting Ruangan Hari Ke-{{ $hariKe }}
                </h2>
                <div class="flex items-center gap-3 text-xs font-bold text-zinc-500 dark:text-zinc-400 mt-1 flex-wrap">
                    <span class="flex items-center gap-1">
                        <i class="bi bi-calendar-check text-indigo-600"></i>
                        <strong>{{ $namaHari }}, {{ $tanggalFormat }}</strong>
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1 text-orange-600 dark:text-orange-400">
                        6 IBT: <strong>{{ $jadwalIbt->pluck('nama_mapel')->implode(', ') ?: '-' }}</strong>
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                        3 TSA: <strong>{{ $jadwalTsa->pluck('nama_mapel')->implode(', ') ?: '-' }}</strong>
                    </span>
                </div>
            </div>

            <!-- Toolbar Aksi -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Tombol Acak / Random Plotting NISM -->
                <button type="button" onclick="confirmRandomPlotting()"
                    class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-black flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                    <i class="bi bi-shuffle text-sm"></i>
                    <span>Acak / Random NISM Peserta</span>
                </button>

                <!-- Tombol Cetak Mading -->
                <a href="{{ route('plotting-imni.cetak-mading', ['tanggal' => $tanggal, 'tahun_id' => $selectedTahunId]) }}"
                    target="_blank"
                    class="px-4 py-2.5 rounded-2xl bg-zinc-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white text-xs font-black flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                    <i class="bi bi-printer text-sm"></i>
                    <span>Cetak Lembar Mading</span>
                </a>
            </div>
        </div>

        @php
            $filledPengawasCount = $pengawasRuangan
                ->filter(fn($p) => !empty($p->ustadz_id) || !empty($p->nama_pengawas))
                ->count();
            $totalRuanganCount = $daftarRuanganImni->count();
        @endphp

        <!-- 2. FORM PENGATURAN PENGAWAS RUANGAN PER RUANGAN -->
        <div class="m3-glass-card p-5 rounded-2xl md:rounded-3xl border border-indigo-500/20 bg-indigo-500/5">
            <form action="{{ route('plotting-imni.simpan-pengawas') }}" method="POST" id="formPengawasHarian"
                class="m-0">
                @csrf
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunId }}">
                <input type="hidden" name="tanggal_ujian" value="{{ $tanggal }}">

                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-indigo-500/10">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-base shadow-sm shrink-0">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4
                                    class="text-xs font-black uppercase tracking-wider text-indigo-900 dark:text-indigo-200">
                                    Penugasan Pengawas Ujian (Hari Ke-{{ $hariKe }})
                                </h4>
                                @if ($totalRuanganCount > 0 && $filledPengawasCount >= $totalRuanganCount)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                        <i class="bi bi-check-circle-fill"></i> Lengkap
                                        ({{ $filledPengawasCount }}/{{ $totalRuanganCount }} Ruangan)
                                    </span>
                                @elseif ($filledPengawasCount > 0)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                        <i class="bi bi-exclamation-circle-fill"></i> Terisi
                                        {{ $filledPengawasCount }}/{{ $totalRuanganCount }} Ruangan
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-zinc-500/15 text-zinc-500 dark:text-zinc-400 border border-zinc-500/30">
                                        <i class="bi bi-dash-circle"></i> Belum Diatur (0/{{ $totalRuanganCount }}
                                        Ruangan)
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                Tentukan ustadz / pengawas untuk masing-masing ruangan ujian pada tanggal ini.
                            </p>
                        </div>
                    </div>

                    <button type="submit" id="btnSimpanPengawas"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black flex items-center gap-2 shadow-sm transition-all cursor-pointer self-start sm:self-auto">
                        <i class="bi bi-save2"></i>
                        <span>Simpan Pengawas</span>
                    </button>
                </div>

                <!-- Grid Input Pengawas per Ruangan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach ($daftarRuanganImni as $rg)
                        @php
                            $pw = $pengawasRuangan->get($rg->id);
                        @endphp
                        <div
                            class="p-3 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="font-black text-xs text-indigo-600 dark:text-indigo-400 font-mono">
                                    {{ $rg->nama_ruangan_imni ?: $rg->nama_ruangan }}
                                </span>
                                <span class="text-[10px] text-zinc-400 font-medium">
                                    {{ $rg->ruanganFisik?->nama_ruangan ?? 'Kelas Fisik' }}
                                </span>
                            </div>

                            <select name="pengawas[{{ $rg->id }}]"
                                class="m3-input-glass w-full text-xs font-bold py-1.5 px-2.5">
                                <option value="">-- Pilih Pengawas --</option>
                                @foreach ($daftarUstadz as $u)
                                    <option value="{{ $u->id }}"
                                        {{ $pw && $pw->ustadz_id == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_lengkap }} ({{ $u->nigm ?? 'Ustadz' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- 3. STATISTIK RINGKAS PLOTTING HARI INI -->
        <div
            class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 text-xs font-bold flex-wrap">
            <div class="flex items-center gap-3">
                <span class="text-zinc-600 dark:text-zinc-400">Total Peserta Terplot:</span>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-600 text-white font-mono font-black">
                    {{ $totalTerplot }} / {{ $totalGabungan }} Murid
                </span>
            </div>

            <div class="flex items-center gap-3 text-zinc-500 text-[11px]">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    6 IBT: <strong class="font-mono text-zinc-700 dark:text-zinc-300">{{ $pesertaIbtCount }}</strong>
                </span>
                <span>•</span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    3 TSA: <strong class="font-mono text-zinc-700 dark:text-zinc-300">{{ $pesertaTsaCount }}</strong>
                </span>
                <span>•</span>
                <span>Ruangan: <strong
                        class="font-mono text-zinc-700 dark:text-zinc-300">{{ $daftarRuanganImni->count() }}</strong></span>
            </div>
        </div>

        <!-- 4. GRID HASIL PLOTTING PESERTA PER RUANGAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($daftarRuanganImni as $rg)
                @php
                    $listPeserta = $pesertaPerRuangan->get($rg->id) ?? collect();
                    $ibtInRoom = $listPeserta->filter(fn($p) => $p->pesertaImni?->tingkat_id == 2)->count();
                    $tsaInRoom = $listPeserta->filter(fn($p) => $p->pesertaImni?->tingkat_id == 3)->count();
                    $pw = $pengawasRuangan->get($rg->id);
                @endphp
                <div
                    class="m3-glass-card rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs border border-zinc-200/80 dark:border-zinc-800 flex flex-col">
                    <!-- Card Header -->
                    <div
                        class="p-4 bg-zinc-50/80 dark:bg-zinc-950/70 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-mono font-black flex items-center justify-center text-sm shadow-2xs">
                                {{ $rg->nama_ruangan_imni ?: $rg->nama_ruangan }}
                            </span>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-zinc-900 dark:text-white">
                                    {{ $rg->ruanganFisik?->nama_ruangan ?? 'Ruangan Fisik' }}
                                </h4>
                                <p class="text-[10.5px] font-medium flex items-center gap-1.5 mt-0.5">
                                    <span class="text-zinc-400">Pengawas:</span>
                                    @if ($pw && (!empty($pw->ustadz_id) || !empty($pw->nama_pengawas)))
                                        <strong
                                            class="text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1">
                                            <i class="bi bi-person-check-fill text-xs text-emerald-600"></i>
                                            {{ $pw->nama_pengawas_efektif }}
                                        </strong>
                                    @else
                                        <span
                                            class="text-amber-600 dark:text-amber-400 font-semibold italic text-[10px] flex items-center gap-1">
                                            <i class="bi bi-exclamation-circle text-xs"></i>
                                            Belum Ditugaskan
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Badge Jumlah Murid -->
                        <div class="flex items-center gap-1 text-[10px] font-mono font-bold">
                            <span
                                class="px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20"
                                title="Peserta 6 IBT">
                                {{ $ibtInRoom }} IBT
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"
                                title="Peserta 3 TSA">
                                {{ $tsaInRoom }} TSA
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-black"
                                title="Total Murid di Ruangan Ini">
                                {{ $listPeserta->count() }} Murid
                            </span>
                        </div>
                    </div>

                    <!-- Card Body / Tabel Peserta -->
                    <div class="overflow-x-auto flex-1 max-h-96 custom-scrollbar">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="bg-zinc-100/70 dark:bg-zinc-900/80 text-zinc-500 dark:text-zinc-400 uppercase font-black text-[9.5px] tracking-wider border-b border-zinc-200/60 dark:border-zinc-800/60 sticky top-0 z-10 backdrop-blur-md">
                                <tr>
                                    <th class="py-2.5 px-3 text-center w-12">Meja</th>
                                    <th class="py-2.5 px-3 w-28">NISM</th>
                                    <th class="py-2.5 px-3">Nama Peserta</th>
                                    <th class="py-2.5 px-3 text-center w-24">Ruangan Asal</th>
                                    <th class="py-2.5 px-3 text-center w-16">Pindah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                @forelse ($listPeserta as $p)
                                    @php
                                        $ruanganAsalNama = $p->pesertaImni?->ruanganAsal?->nama_ruangan ?? $p->pesertaImni?->level?->nama_level ?? ($p->pesertaImni?->tingkat_id == 3 ? '3 TSA' : ($p->pesertaImni?->tingkat_id == 2 ? '6 IBT' : '-'));
                                        $isTsa = (str_contains(strtoupper($ruanganAsalNama), 'TSA') || $p->pesertaImni?->tingkat_id == 3);
                                        $isIbt = (str_contains(strtoupper($ruanganAsalNama), '6') || $p->pesertaImni?->tingkat_id == 2);
                                    @endphp
                                    <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40 transition-colors">
                                        <!-- No Meja -->
                                        <td
                                            class="py-2.5 px-3 text-center font-mono font-black text-indigo-600 dark:text-indigo-400 text-xs">
                                            {{ $loop->iteration }}
                                        </td>

                                        <!-- NISM -->
                                        <td
                                            class="py-2.5 px-3 font-mono font-bold text-zinc-500 dark:text-zinc-400 text-[11px]">
                                            {{ $p->pesertaImni?->murid?->nism ?? '-' }}
                                        </td>

                                        <!-- Nama Murid -->
                                        <td class="py-2.5 px-3 font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                                            {{ $p->pesertaImni?->murid?->nama_lengkap ?? '-' }}
                                        </td>

                                        <!-- Ruangan Asal (3 TSA / 6-A / 6-B) -->
                                        <td class="py-2.5 px-3 text-center">
                                            @if ($isTsa)
                                                <span
                                                    class="inline-block px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-[10px] border border-blue-500/20">
                                                    {{ $ruanganAsalNama }}
                                                </span>
                                            @elseif ($isIbt)
                                                <span
                                                    class="inline-block px-2 py-0.5 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400 font-black text-[10px] border border-orange-500/20">
                                                    {{ $ruanganAsalNama }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-block px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 font-black text-[10px] border border-emerald-500/20">
                                                    {{ $ruanganAsalNama }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Aksi Pindah Ruangan Cepat -->
                                        <td class="py-2.5 px-3 text-center">
                                            <select onchange="pindahRuanganPeserta({{ $p->id }}, this.value)"
                                                class="text-[10px] font-bold py-1 px-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg border-0 cursor-pointer text-zinc-700 dark:text-zinc-300">
                                                <option value="" disabled selected>⇄</option>
                                                @foreach ($daftarRuanganImni as $targetRg)
                                                    @if ($targetRg->id != $rg->id)
                                                        <option value="{{ $targetRg->id }}">
                                                            Ke
                                                            {{ $targetRg->nama_ruangan_imni ?: $targetRg->nama_ruangan }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-zinc-400 italic text-xs">
                                            Belum ada peserta di ruangan ini. Klik <strong>"Acak / Random NISM"</strong>
                                            untuk plotting.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-400">
                    Belum ada master Ruangan IMNI. Silakan buat master ruangan terlebih dahulu.
                </div>
            @endforelse
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC ACAK, PENGAWAS & PINDAH RUANGAN -->
    <script>
        // Submit AJAX Form Pengawas Harian
        document.getElementById('formPengawasHarian')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = document.getElementById('btnSimpanPengawas');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Menyimpan...';
            }

            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }

                    if (data.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: data.message,
                                timer: 1400,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert(data.message);
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menyimpan pengawas.',
                                'error');
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menyimpan pengawas.');
                        }
                    }
                })
                .catch(err => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error!', 'Gagal menghubungi server: ' + err, 'error');
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                });
        });

        function confirmRandomPlotting() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Acak / Randomize NISM Peserta?',
                    text: 'Sistem akan mengacak seluruh murid Kelas 6 IBT dan 3 TSA secara merata dan mencampur urutan duduknya ke ruangan R1..R{{ $daftarRuanganImni->count() }} untuk hari ini.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Acak Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeRandomPlotting();
                    }
                });
            } else {
                if (confirm('Acak seluruh murid 6 IBT & 3 TSA ke ruangan ujian untuk hari ini?')) {
                    executeRandomPlotting();
                }
            }
        }

        function executeRandomPlotting() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mengacak Peserta...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            fetch("{{ route('plotting-imni.random') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tahun_pelajaran_id: {{ $selectedTahunId }},
                        tanggal_ujian: '{{ $tanggal }}'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert(data.message);
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                        } else {
                            alert(data.message || 'Terjadi kesalahan.');
                        }
                    }
                })
                .catch(err => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error!', 'Gagal menghubungi server: ' + err, 'error');
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                });
        }

        function pindahRuanganPeserta(pesertaRuanganId, targetRuanganId) {
            if (!targetRuanganId) return;

            fetch("{{ route('plotting-imni.pindah-ruangan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        peserta_ruangan_id: pesertaRuanganId,
                        target_ruangan_id: targetRuanganId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal memindahkan peserta.');
                    }
                })
                .catch(err => {
                    alert('Gagal memindahkan peserta.');
                });
        }
    </script>
</x-app-layout>
