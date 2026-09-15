@section('title', 'Buat Tanda Tangan')

<x-app-layout>
    <!-- Header Page (Compact M3) -->
    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('administrator.index') }}"
                class="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200/80 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-xl flex items-center justify-center transition-all shadow-2xs active:scale-95 shrink-0 outline-none"
                title="Kembali">
                <i class="bi bi-arrow-left text-base font-bold"></i>
            </a>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    Buat Tanda Tangan
                </h2>
                <p class="text-xs md:text-[13px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Untuk: <span
                        class="text-primary dark:text-primary-dark font-black">{{ $administrator->nama_lengkap }}</span>
                    ({{ $administrator->user?->roles?->first()?->name ?? 'Staff Admin' }})
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card M3 -->
    <div class="m3-glass-card p-5 sm:p-7 relative overflow-hidden flex flex-col items-center shadow-2xs">

        <form id="signature-form" action="{{ route('administrator.signature.update', $administrator->id) }}"
            method="POST" class="w-full max-w-2xl">
            @csrf

            <!-- Hidden input untuk menampung data gambar -->
            <input type="hidden" name="tanda_tangan_base64" id="tanda_tangan_base64">

            <!-- Canvas Area -->
            <div
                class="relative w-full h-56 sm:h-72 rounded-2xl overflow-hidden border-2 border-dashed border-zinc-300 dark:border-zinc-700 bg-white/40 dark:bg-black/50 cursor-crosshair touch-none shadow-2xs">

                <!-- OVERLAY TANDA TANGAN LAMA (Jika Sudah Ada) -->
                @if ($administrator->tanda_tangan)
                    <div id="existing-signature-overlay"
                        class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/95 dark:bg-black/95 backdrop-blur-md transition-all duration-300">

                        <img src="{{ asset('storage/' . $administrator->tanda_tangan) }}"
                            class="h-1/2 sm:h-3/5 object-contain drop-shadow-sm dark:drop-shadow-none mb-3 filter dark:invert dark:opacity-90"
                            alt="Tanda Tangan Lama">

                        <span
                            class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-xl mb-3 shadow-2xs">
                            <i class="bi bi-check-circle-fill mr-1"></i> Tanda Tangan Tersimpan
                        </span>

                        <!-- Tombol Gambar Ulang -->
                        <button type="button" id="btn-redraw"
                            class="h-9 px-4 bg-primary/10 hover:bg-primary/20 dark:bg-primary-dark/10 dark:hover:bg-primary-dark/20 text-primary dark:text-primary-dark border border-primary/20 rounded-xl font-black text-xs transition-all active:scale-95 outline-none flex items-center justify-center shadow-2xs">
                            <i class="bi bi-pencil-square mr-1.5"></i> Gambar Ulang
                        </button>
                    </div>
                @endif

                <canvas id="signature-pad" class="w-full h-full absolute inset-0 z-10"></canvas>

                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0"
                    id="canvas-placeholder">
                    <span
                        class="text-xs font-black uppercase tracking-widest text-zinc-300 dark:text-zinc-600 select-none">
                        Area Tanda Tangan Digital
                    </span>
                </div>
            </div>

            <p class="text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-3.5">
                Gunakan mouse atau sentuhan jari Anda untuk menggambar di dalam kotak kanvas.
            </p>

            <!-- Action Buttons (Compact M3) -->
            <div class="flex flex-col sm:flex-row justify-center items-center gap-2.5 mt-6 w-full">
                <!-- Bersihkan -->
                <button type="button" id="btn-clear"
                    class="h-10 w-full sm:w-auto px-5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl font-black text-xs transition-all active:scale-95 flex items-center justify-center shadow-2xs outline-none">
                    <i class="bi bi-eraser-fill mr-1.5"></i> Bersihkan
                </button>

                <!-- Unduh File -->
                <button type="button" id="btn-download"
                    class="h-10 w-full sm:w-auto px-5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 rounded-xl font-black text-xs transition-all active:scale-95 flex items-center justify-center shadow-2xs outline-none">
                    <i class="bi bi-download mr-1.5"></i> Unduh (.png)
                </button>

                <!-- Simpan -->
                <button type="button" id="btn-submit"
                    class="m3-btn-primary w-full sm:w-auto h-10 px-6 text-xs font-black shadow-2xs">
                    <i class="bi bi-save2-fill mr-1.5"></i> Simpan Tanda Tangan
                </button>
            </div>
        </form>
    </div>

    @push('script')
        <!-- CDN Signature Pad -->
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('signature-pad');
                const placeholder = document.getElementById('canvas-placeholder');
                const btnClear = document.getElementById('btn-clear');
                const btnDownload = document.getElementById('btn-download');
                const btnSubmit = document.getElementById('btn-submit');
                const form = document.getElementById('signature-form');
                const inputBase64 = document.getElementById('tanda_tangan_base64');
                const overlay = document.getElementById('existing-signature-overlay');
                const btnRedraw = document.getElementById('btn-redraw');

                // Konfigurasi SweetAlert M3 OLED
                const isDark = document.documentElement.classList.contains('dark');
                const swalBg = isDark ? '#0c0c0e' : '#ffffff';
                const swalColor = isDark ? '#f4f4f5' : '#18181b';

                const customSwal = Swal.mixin({
                    background: swalBg,
                    color: swalColor,
                    buttonsStyling: false,
                    heightAuto: false,
                    customClass: {
                        popup: "!rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl p-6 transition-colors duration-300",
                        confirmButton: "h-10 px-5 bg-primary dark:bg-primary-dark text-white dark:text-zinc-900 font-black text-xs rounded-xl shadow-2xs active:scale-95 transition-all outline-none mt-3"
                    }
                });

                // Inisialisasi Signature Pad
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: isDark ? '#f4f4f5' : '#18181b',
                    minWidth: 1.0,
                    maxWidth: 2.5
                });

                // Handle Resize Canvas
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    signaturePad.clear();
                    placeholder.style.display = 'flex';
                }

                window.addEventListener("resize", resizeCanvas);

                if (!overlay) {
                    resizeCanvas();
                }

                if (btnRedraw) {
                    btnRedraw.addEventListener('click', () => {
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                        setTimeout(() => {
                            overlay.remove();
                            resizeCanvas();
                        }, 300);
                    });
                }

                signaturePad.addEventListener("beginStroke", () => {
                    placeholder.style.display = 'none';
                });

                btnClear.addEventListener('click', () => {
                    signaturePad.clear();
                    placeholder.style.display = 'flex';
                });

                // FUNGSI AUTO-CROP: Memotong Ruang Kosong Transparan
                function getCroppedSignature() {
                    const ctx = canvas.getContext('2d');
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const data = imageData.data;

                    let minX = canvas.width,
                        minY = canvas.height,
                        maxX = 0,
                        maxY = 0;
                    let hasContent = false;

                    for (let y = 0; y < canvas.height; y++) {
                        for (let x = 0; x < canvas.width; x++) {
                            const alpha = data[(y * canvas.width + x) * 4 + 3];
                            if (alpha > 0) {
                                hasContent = true;
                                if (x < minX) minX = x;
                                if (x > maxX) maxX = x;
                                if (y < minY) minY = y;
                                if (y > maxY) maxY = y;
                            }
                        }
                    }

                    if (!hasContent) return null;

                    const padding = 15 * ratio;
                    minX = Math.max(0, minX - padding);
                    minY = Math.max(0, minY - padding);
                    maxX = Math.min(canvas.width, maxX + padding);
                    maxY = Math.min(canvas.height, maxY + padding);

                    const cropWidth = maxX - minX;
                    const cropHeight = maxY - minY;

                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = cropWidth;
                    tempCanvas.height = cropHeight;
                    const tempCtx = tempCanvas.getContext('2d');

                    tempCtx.drawImage(canvas, minX, minY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
                    return tempCanvas.toDataURL('image/png');
                }

                // Event Tombol Unduh
                btnDownload.addEventListener('click', () => {
                    if (document.getElementById('existing-signature-overlay')) {
                        const oldImgSrc = document.querySelector('#existing-signature-overlay img').src;
                        const link = document.createElement('a');
                        link.download = 'TTD_{{ Str::slug($administrator->nama_lengkap) }}_Lama.png';
                        link.href = oldImgSrc;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        return;
                    }

                    if (signaturePad.isEmpty()) {
                        customSwal.fire({
                            icon: 'warning',
                            title: '<span class="text-base font-black tracking-tight">Kanvas Kosong</span>',
                            html: '<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Silakan gambar tanda tangan Anda terlebih dahulu sebelum mengunduh.</p>'
                        });
                        return;
                    }

                    const croppedDataURL = getCroppedSignature();
                    const link = document.createElement('a');
                    link.download = 'TTD_{{ Str::slug($administrator->nama_lengkap) }}.png';
                    link.href = croppedDataURL;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });

                // Event Tombol Simpan
                btnSubmit.addEventListener('click', (e) => {
                    if (document.getElementById('existing-signature-overlay')) {
                        e.preventDefault();
                        customSwal.fire({
                            icon: 'info',
                            heightAuto: false,
                            title: '<span class="text-base font-black tracking-tight">Sudah Tersimpan</span>',
                            html: '<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Tanda tangan ini sudah ada di database. Klik <b>Gambar Ulang</b> jika ingin mengubahnya.</p>'
                        });
                        return;
                    }

                    if (signaturePad.isEmpty()) {
                        e.preventDefault();
                        customSwal.fire({
                            icon: 'warning',
                            title: '<span class="text-base font-black tracking-tight">Tanda Tangan Kosong</span>',
                            heightAuto: false,
                            html: '<p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-1">Silakan gambar tanda tangan Anda terlebih dahulu!</p>'
                        });
                        return;
                    }

                    customSwal.fire({
                        title: '<span class="text-base font-black tracking-tight">Menyimpan...</span>',
                        heightAuto: false,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const croppedDataURL = getCroppedSignature();
                    inputBase64.value = croppedDataURL;
                    form.submit();
                });
            });
        </script>
    @endpush
</x-app-layout>
