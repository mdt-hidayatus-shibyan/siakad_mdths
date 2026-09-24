@php
    $defaultFoto = $murid->jenis_kelamin == 'L' ? 'laki-default.png' : 'perempuan-default.png';
    $fotoPath = $murid->foto ? $murid->foto : $defaultFoto;
@endphp

<!-- Form Upload & Crop Foto AJAX Modal -->
<form id="formCropUploadFoto" action="{{ route('murid.updateFoto', $murid->id) }}" method="POST"
    enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[92vh]">
    @csrf
    @method('PATCH')

    <!-- Hidden Input for Cropped Image Blob -->
    <input type="file" name="foto" id="croppedFotoInput" class="hidden" accept="image/jpeg,image/png,image/webp">

    <!-- Modal Header -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-b border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-between transition-colors duration-300">
        <div class="flex items-center gap-2.5">
            <div
                class="w-8 h-8 rounded-xl bg-primary/10 dark:bg-primary-dark/20 text-primary dark:text-primary-dark flex items-center justify-center shadow-2xs">
                <i class="bi bi-camera-fill text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                    Foto Murid (3x4 cm)
                </h3>
                <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ $ruangan->nama_ruangan }} &bull; {{ $murid->nama_lengkap }}
                </p>
            </div>
        </div>

        <!-- Tombol Tutup Modal -->
        <button type="button" data-dismiss="modal" command="close" onclick="cleanupCameraAndCropper()"
            class="min-w-[36px] min-h-[36px] flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-200/60 dark:hover:bg-zinc-800 text-zinc-500 dark:text-zinc-400 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-4 md:p-5 transition-colors duration-300 overflow-y-auto custom-scrollbar flex-1 space-y-4">

        <!-- 1. TAB SELECTION (Upload File vs Ambil Kamera) - Sembunyikan saat dalam Crop Mode -->
        <div id="sourceTabsContainer"
            class="flex gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800/70 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60">
            <button type="button" id="tabUploadBtn" onclick="switchPhotoSource('upload')"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs border border-zinc-200/60 dark:border-zinc-700">
                <i class="bi bi-cloud-arrow-up-fill text-primary dark:text-primary-dark text-sm"></i>
                <span>Unggah File</span>
            </button>
            <button type="button" id="tabCameraBtn" onclick="switchPhotoSource('camera')"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
                <i class="bi bi-camera-video-fill text-sm"></i>
                <span>Ambil dari Kamera</span>
            </button>
        </div>

        <!-- 2. PANEL A: UNGGAH FILE DARI PERANGKAT -->
        <div id="panelUploadFile" class="space-y-3">
            <div
                class="w-full bg-zinc-50/50 dark:bg-zinc-900/40 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-3xl p-6 flex flex-col items-center justify-center text-center relative group hover:border-primary dark:hover:border-primary-dark transition-all duration-300 cursor-pointer">

                <input type="file" id="fileFotoSelector" accept="image/png, image/jpeg, image/jpg, image/webp"
                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10"
                    onchange="handleFileChosen(this)">

                <div
                    class="w-16 h-16 rounded-2xl bg-primary/10 dark:bg-primary-dark/15 text-primary dark:text-primary-dark flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                    <i class="bi bi-image-fill text-2xl"></i>
                </div>

                <h4 class="text-sm font-black text-zinc-900 dark:text-white mb-1">
                    Pilih File Foto atau Tarik ke Sini
                </h4>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 max-w-xs">
                    Format JPG, JPEG, PNG, atau WEBP. Foto akan otomatis masuk ke pemotong rasio 3x4 cm.
                </p>

                <!-- Foto Saat Ini & Tombol Hapus -->
                <div
                    class="mt-4 pt-3 border-t border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3 text-left w-full max-w-sm">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('storage/' . $fotoPath) }}" alt="Current Foto"
                            class="w-8 h-10 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                        <div>
                            <span
                                class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block">Foto
                                Saat Ini</span>
                            <span
                                class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $murid->nama_panggilan ?: $murid->nama_lengkap }}</span>
                        </div>
                    </div>

                    @if ($murid->foto)
                        <button type="button" onclick="hapusFotoMurid()"
                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-xs font-bold transition-all flex items-center gap-1.5 active:scale-95">
                            <i class="bi bi-trash3-fill text-xs"></i>
                            <span>Hapus Foto</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. PANEL B: AMBIL FOTO DARI KAMERA / WEBCAM -->
        <div id="panelCamera" class="hidden space-y-3">
            <div
                class="relative bg-black rounded-3xl overflow-hidden shadow-2xl aspect-[4/3] max-h-[340px] flex items-center justify-center border border-zinc-800">

                <!-- Video Element -->
                <video id="cameraStreamVideo" autoplay playsinline muted
                    class="w-full h-full object-cover transform -scale-x-100"></video>

                <!-- Grid & Bingkai Panduan Pasfoto 3:4 -->
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div
                        class="w-[50%] h-[85%] border-2 border-dashed border-primary dark:border-primary-dark rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.5)] flex flex-col items-center justify-between p-2">
                        <span
                            class="text-[9px] font-black uppercase tracking-widest text-primary dark:text-primary-dark bg-black/70 px-2 py-0.5 rounded-md">
                            Kepala & Wajah
                        </span>
                        <span
                            class="text-[9px] font-black uppercase tracking-widest text-primary dark:text-primary-dark bg-black/70 px-2 py-0.5 rounded-md">
                            Bahu & Dada (3x4)
                        </span>
                    </div>
                </div>

                <!-- Loading State Kamera -->
                <div id="cameraLoading"
                    class="absolute inset-0 bg-black flex flex-col items-center justify-center text-white z-20">
                    <i class="bi bi-camera-video animate-pulse text-3xl text-primary mb-2"></i>
                    <span class="text-xs font-bold">Mengakses Kamera...</span>
                </div>

                <!-- Pesan Error Kamera -->
                <div id="cameraErrorMsg"
                    class="hidden absolute inset-0 bg-zinc-900/95 p-6 flex flex-col items-center justify-center text-center text-white z-30 space-y-2">
                    <i class="bi bi-exclamation-triangle-fill text-3xl text-amber-500"></i>
                    <p class="text-xs font-bold text-zinc-200" id="cameraErrorText">Kamera tidak dapat diakses.</p>
                    <p class="text-[11px] text-zinc-400">Pastikan izin kamera telah diizinkan pada browser Anda.</p>
                    <button type="button" onclick="startCameraStream()"
                        class="mt-2 px-4 py-1.5 rounded-xl bg-primary text-black font-black text-xs">
                        Coba Lagi
                    </button>
                </div>
            </div>

            <!-- Kontrol Kamera -->
            <div class="flex items-center justify-between gap-2 px-1">
                <!-- Pilihan Device Kamera jika lebih dari satu -->
                <div class="flex-1 max-w-[200px]">
                    <select id="cameraDeviceSelect" onchange="startCameraStream(this.value)"
                        class="m3-input-glass !py-1.5 !text-xs !rounded-xl w-full">
                        <option value="">Kamera Utama</option>
                    </select>
                </div>

                <!-- Tombol Jepret Foto -->
                <button type="button" id="snapPhotoBtn" onclick="takeSnapshotFromCamera()"
                    class="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs flex items-center gap-2 shadow-md active:scale-95 transition-all">
                    <i class="bi bi-camera-fill text-sm"></i>
                    <span>Jepret Foto</span>
                </button>
            </div>
        </div>

        <!-- 4. PANEL C: AREA CROPPER 3x4 CM -->
        <div id="panelCropStage" class="hidden space-y-3">
            <div
                class="flex items-center justify-between bg-primary/10 dark:bg-primary-dark/15 px-3.5 py-2 rounded-xl border border-primary/20">
                <span class="text-xs font-black text-primary dark:text-primary-dark flex items-center gap-1.5">
                    <i class="bi bi-crop text-sm"></i> Atur & Potong Foto (Rasio 3x4)
                </span>
                <button type="button" onclick="cancelCropToSource()"
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i> Ganti Foto
                </button>
            </div>

            <!-- Cropper Box Canvas Container -->
            <div
                class="w-full bg-zinc-950 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 relative h-[300px] flex items-center justify-center">
                <img id="imageToCrop" src="" class="max-w-full max-h-full block">
            </div>

            <!-- Toolbar Kontrol Cropper -->
            <div
                class="flex flex-wrap items-center justify-between gap-2 bg-zinc-50/80 dark:bg-zinc-900/60 p-2.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800">
                <!-- Rotate & Zoom Controls -->
                <div class="flex items-center gap-1">
                    <button type="button" onclick="rotateCropper(-90)" title="Putar Kiri 90°"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" onclick="rotateCropper(90)" title="Putar Kanan 90°"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" onclick="zoomCropper(0.1)" title="Perbesar"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button type="button" onclick="zoomCropper(-0.1)" title="Perkecil"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" onclick="resetCropper()" title="Reset Posisi"
                        class="px-2.5 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        Reset
                    </button>
                </div>

                <div class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500">
                    Standar Pasfoto: <span class="text-zinc-800 dark:text-zinc-200 font-extrabold">3 x 4 cm</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Hidden Canvas untuk capture snapshot kamera -->
    <canvas id="snapshotCanvas" class="hidden"></canvas>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-between transition-colors duration-300">
        <div>
            @if ($murid->foto)
                <button type="button" onclick="hapusFotoMurid()"
                    class="px-3.5 py-2 rounded-xl font-bold text-xs bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 transition-all flex items-center gap-1.5 active:scale-95 outline-none">
                    <i class="bi bi-trash3 text-xs"></i>
                    <span>Hapus Foto (Set Null)</span>
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" data-dismiss="modal" command="close" onclick="cleanupCameraAndCropper()"
                class="px-4 py-2 rounded-xl font-bold text-xs bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors outline-none">
                Batal
            </button>

            <button type="submit" id="btnSimpanFoto"
                class="m3-btn-primary px-6 py-2 outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="bi bi-check2-circle text-sm"></i>
                <span id="btnSimpanText">Simpan Foto</span>
            </button>
        </div>
    </div>
</form>

<!-- ============================================== -->
<!-- SCRIPT LOGIKA KAMERA & CROPPER 3x4 -->
<!-- ============================================== -->
<script>
    (function() {
        let currentStream = null;
        let cropperInstance = null;
        let activeSource = 'upload'; // 'upload' | 'camera'

        // 1. Ganti Tab Sumber Foto
        window.switchPhotoSource = function(source) {
            activeSource = source;
            const tabUploadBtn = document.getElementById('tabUploadBtn');
            const tabCameraBtn = document.getElementById('tabCameraBtn');
            const panelUpload = document.getElementById('panelUploadFile');
            const panelCamera = document.getElementById('panelCamera');
            const panelCrop = document.getElementById('panelCropStage');

            // Hancurkan cropper jika kembali ke tab
            destroyCropper();
            panelCrop.classList.add('hidden');

            if (source === 'upload') {
                stopCameraStream();
                tabUploadBtn.className =
                    'flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs border border-zinc-200/60 dark:border-zinc-700';
                tabCameraBtn.className =
                    'flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
                panelUpload.classList.remove('hidden');
                panelCamera.classList.add('hidden');
            } else {
                tabCameraBtn.className =
                    'flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs border border-zinc-200/60 dark:border-zinc-700';
                tabUploadBtn.className =
                    'flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
                panelUpload.classList.add('hidden');
                panelCamera.classList.remove('hidden');
                startCameraStream();
            }
        };

        // 2. Inisialisasi Akses Kamera / Webcam
        window.startCameraStream = async function(deviceId = null) {
            const video = document.getElementById('cameraStreamVideo');
            const loading = document.getElementById('cameraLoading');
            const errorMsg = document.getElementById('cameraErrorMsg');
            const deviceSelect = document.getElementById('cameraDeviceSelect');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (errorMsg) {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('cameraErrorText').textContent =
                        'Browser ini tidak mendukung akses kamera (HTTPS diperlukan).';
                }
                return;
            }

            stopCameraStream();
            if (loading) loading.classList.remove('hidden');
            if (errorMsg) errorMsg.classList.add('hidden');

            const constraints = {
                video: {
                    width: {
                        ideal: 1280
                    },
                    height: {
                        ideal: 960
                    },
                    facingMode: deviceId ? undefined : 'user',
                    deviceId: deviceId ? {
                        exact: deviceId
                    } : undefined
                },
                audio: false
            };

            try {
                currentStream = await navigator.mediaDevices.getUserMedia(constraints);
                if (video) {
                    video.srcObject = currentStream;
                    video.onloadedmetadata = () => {
                        video.play();
                        if (loading) loading.classList.add('hidden');
                    };
                }

                // Ambil daftar kamera jika belum diisi
                if (deviceSelect && deviceSelect.options.length <= 1) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                    if (videoDevices.length > 1) {
                        deviceSelect.innerHTML = '';
                        videoDevices.forEach((dev, idx) => {
                            const opt = document.createElement('option');
                            opt.value = dev.deviceId;
                            opt.textContent = dev.label || `Kamera ${idx + 1}`;
                            deviceSelect.appendChild(opt);
                        });
                    }
                }
            } catch (err) {
                if (loading) loading.classList.add('hidden');
                if (errorMsg) {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('cameraErrorText').textContent = 'Gagal mengakses kamera: ' + (
                        err.message || 'Izin ditolak.');
                }
            }
        };

        // 3. Matikan Stream Kamera
        window.stopCameraStream = function() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            const video = document.getElementById('cameraStreamVideo');
            if (video) {
                video.srcObject = null;
            }
        };

        // 4. Jepret Foto dari Kamera
        window.takeSnapshotFromCamera = function() {
            const video = document.getElementById('cameraStreamVideo');
            const canvas = document.getElementById('snapshotCanvas');
            if (!video || !canvas) return;

            const width = video.videoWidth || 640;
            const height = video.videoHeight || 480;

            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            // Cerminkan canvas secara horizontal agar natural sesuai tampilan preview
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, width, height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            stopCameraStream();
            initCropperStage(dataUrl);
        };

        // 5. Tangkap File yang Dipilih dari Disk
        window.handleFileChosen = function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    initCropperStage(e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        // 6. Masuk ke Tahap Crop 3x4
        function initCropperStage(imageUrl) {
            document.getElementById('panelUploadFile').classList.add('hidden');
            document.getElementById('panelCamera').classList.add('hidden');
            document.getElementById('sourceTabsContainer').classList.add('hidden');

            const panelCrop = document.getElementById('panelCropStage');
            panelCrop.classList.remove('hidden');

            const imageEl = document.getElementById('imageToCrop');
            imageEl.src = imageUrl;

            destroyCropper();

            // Inisialisasi Cropper.js dengan Fixed Aspect Ratio 3:4 (Pasfoto 3x4)
            setTimeout(() => {
                cropperInstance = new Cropper(imageEl, {
                    aspectRatio: 3 / 4, // Rasio 3x4 cm
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.88,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    ready: function() {
                        // Cropper siap
                    }
                });
            }, 100);
        }

        // 7. Kontrol Cropper Tools
        window.rotateCropper = function(degree) {
            if (cropperInstance) cropperInstance.rotate(degree);
        };

        window.zoomCropper = function(ratio) {
            if (cropperInstance) cropperInstance.zoom(ratio);
        };

        window.resetCropper = function() {
            if (cropperInstance) cropperInstance.reset();
        };

        window.cancelCropToSource = function() {
            destroyCropper();
            document.getElementById('panelCropStage').classList.add('hidden');
            document.getElementById('sourceTabsContainer').classList.remove('hidden');
            window.switchPhotoSource(activeSource);
        };

        function destroyCropper() {
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
        }

        window.cleanupCameraAndCropper = function() {
            stopCameraStream();
            destroyCropper();
        };

        // Aksi Hapus Foto (Set Null)
        window.hapusFotoMurid = function() {
            Swal.fire({
                title: 'Hapus Foto Murid?',
                text: 'Foto murid akan dihapus dan dikembalikan ke avatar default.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#71717a',
                confirmButtonText: 'Ya, Hapus Foto!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('murid.updateFoto', $murid->id) }}',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content') ||
                                '{{ csrf_token() }}',
                            _method: 'PATCH',
                            hapus_foto: 1
                        },
                        headers: {
                            Accept: 'application/json'
                        },
                        success: function(res) {
                            cleanupCameraAndCropper();
                            window.closeDialogModal();

                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'success',
                                    title: res.message ||
                                        'Foto murid berhasil dihapus!'
                                });
                            }

                            window.refreshDataGrid('#data-table-container');
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message ||
                                'Gagal menghapus foto!';
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'error',
                                    title: msg
                                });
                            } else {
                                alert(msg);
                            }
                        }
                    });
                }
            });
        };

        // 8. Intercept Submit Form untuk mengekstrak Cropped Image 3x4
        const form = document.getElementById('formCropUploadFoto');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Jika cropper sedang aktif, ekstrak gambar hasil crop dengan resolusi pasfoto tinggi (600x800 px)
                if (cropperInstance) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<i class="bi bi-arrow-repeat animate-spin mr-1.5"></i> Memproses...';

                    const canvas = cropperInstance.getCroppedCanvas({
                        width: 600, // Resolusi pasfoto tajam (3x4 ratio: 600 x 800)
                        height: 800,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                            return;
                        }

                        // Buat FormData baru dengan Blob hasil crop
                        const formData = new FormData();
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content') ||
                            '{{ csrf_token() }}');
                        formData.append('_method', 'PATCH');
                        formData.append('foto', blob, 'foto_santri_3x4.jpg');

                        $.ajax({
                            url: form.action,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                Accept: 'application/json'
                            },
                            success: function(response) {
                                cleanupCameraAndCropper();
                                window.closeDialogModal();

                                if (typeof Toast !== 'undefined') {
                                    Toast.fire({
                                        icon: 'success',
                                        title: response.message ||
                                            'Foto 3x4 santri berhasil diperbarui!',
                                    });
                                }

                                // Refresh data table
                                window.refreshDataGrid('#data-table-container');
                            },
                            error: function(xhr) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnHtml;

                                let errorMsg = xhr.responseJSON?.message ||
                                    'Gagal mengunggah foto!';
                                if (xhr.responseJSON?.errors?.foto) {
                                    errorMsg = xhr.responseJSON.errors.foto[0];
                                }

                                if (typeof Toast !== 'undefined') {
                                    Toast.fire({
                                        icon: 'error',
                                        title: errorMsg
                                    });
                                } else {
                                    alert(errorMsg);
                                }
                            }
                        });
                    }, 'image/jpeg', 0.92);
                }
            }, true);
        }

        // Listener jika modal ditutup via escape / backdrop
        $(document).one('click', '[data-dismiss="modal"], el-dialog-backdrop', function() {
            cleanupCameraAndCropper();
        });
    })();
</script>
