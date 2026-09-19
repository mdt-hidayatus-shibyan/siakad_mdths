@php
    $defaultFoto = $ustadz->jenis_kelamin == 'L' ? 'assets/laki-default.png' : 'assets/perempuan-default.png';
    $fotoPath = $ustadz->foto ? asset('storage/' . $ustadz->foto) : asset($defaultFoto);
@endphp

<!-- Form Upload & Crop Foto Ustadz AJAX Modal -->
<form id="formCropUploadFotoUstadz" action="{{ route('ustadz.update-foto', $ustadz->id) }}" method="POST"
    enctype="multipart/form-data" class="ajax-form relative z-10 flex flex-col max-h-[92vh]">
    @csrf

    <!-- Hidden Input for Cropped Image Blob -->
    <input type="file" name="foto" id="croppedFotoInputUstadz" class="hidden" accept="image/jpeg,image/png,image/webp">

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
                    Foto Profil Ustadz / Guru
                </h3>
                <p class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ $ustadz->jenis_kelamin === 'L' ? 'Ust.' : 'Ustd.' }} {{ $ustadz->nama_lengkap }} (Kode:
                    {{ $ustadz->kode_ustadz ?? '-' }})
                </p>
            </div>
        </div>

        <!-- Tombol Tutup Modal -->
        <button type="button" data-dismiss="modal" command="close" onclick="cleanupCameraAndCropperUstadz()"
            class="min-w-[36px] min-h-[36px] flex items-center justify-center rounded-xl bg-transparent hover:bg-zinc-200/60 dark:hover:bg-zinc-800 text-zinc-500 dark:text-zinc-400 transition-colors duration-200 outline-none">
            <i class="bi bi-x-lg text-xs font-bold"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-4 md:p-5 transition-colors duration-300 overflow-y-auto custom-scrollbar flex-1 space-y-4">

        <!-- 1. TAB SELECTION (Upload File vs Ambil Kamera) -->
        <div id="sourceTabsContainerUstadz"
            class="flex gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800/70 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60">
            <button type="button" id="tabUploadBtnUstadz" onclick="switchPhotoSourceUstadz('upload')"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs border border-zinc-200/60 dark:border-zinc-700">
                <i class="bi bi-cloud-arrow-up-fill text-primary dark:text-primary-dark text-sm"></i>
                <span>Unggah File</span>
            </button>
            <button type="button" id="tabCameraBtnUstadz" onclick="switchPhotoSourceUstadz('camera')"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
                <i class="bi bi-camera-video-fill text-sm"></i>
                <span>Ambil dari Kamera</span>
            </button>
        </div>

        <!-- 2. PANEL A: UNGGAH FILE DARI PERANGKAT -->
        <div id="panelUploadFileUstadz" class="space-y-3">
            <div
                class="w-full bg-zinc-50/50 dark:bg-zinc-900/40 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-3xl p-6 flex flex-col items-center justify-center text-center relative group hover:border-primary dark:hover:border-primary-dark transition-all duration-300 cursor-pointer">

                <input type="file" id="fileFotoSelectorUstadz" accept="image/png, image/jpeg, image/jpg, image/webp"
                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10"
                    onchange="handleFileChosenUstadz(this)">

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
                        <img src="{{ $fotoPath }}" alt="Current Foto"
                            class="w-9 h-11 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                        <div>
                            <span
                                class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block">Foto
                                Saat Ini</span>
                            <span
                                class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $ustadz->foto ? 'Foto Tersedia' : 'Default Avatar' }}</span>
                        </div>
                    </div>

                    @if ($ustadz->foto)
                        <button type="button" onclick="hapusFotoUstadz()"
                            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-xs font-bold transition-all flex items-center gap-1.5 active:scale-95">
                            <i class="bi bi-trash3-fill text-xs"></i>
                            <span>Hapus Foto</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. PANEL B: AMBIL FOTO DARI KAMERA / WEBCAM -->
        <div id="panelCameraUstadz" class="hidden space-y-3">
            <div
                class="relative bg-black rounded-3xl overflow-hidden shadow-2xl aspect-[4/3] max-h-[340px] flex items-center justify-center border border-zinc-800">

                <!-- Video Element -->
                <video id="cameraStreamVideoUstadz" autoplay playsinline muted
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
                <div id="cameraLoadingUstadz"
                    class="absolute inset-0 bg-black flex flex-col items-center justify-center text-white z-20">
                    <i class="bi bi-camera-video animate-pulse text-3xl text-primary mb-2"></i>
                    <span class="text-xs font-bold">Mengakses Kamera...</span>
                </div>

                <!-- Pesan Error Kamera -->
                <div id="cameraErrorMsgUstadz"
                    class="hidden absolute inset-0 bg-zinc-900/95 p-6 flex flex-col items-center justify-center text-center text-white z-30 space-y-2">
                    <i class="bi bi-exclamation-triangle-fill text-3xl text-amber-500"></i>
                    <p class="text-xs font-bold text-zinc-200" id="cameraErrorTextUstadz">Kamera tidak dapat diakses.
                    </p>
                    <p class="text-[11px] text-zinc-400">Pastikan izin kamera telah diizinkan pada browser Anda.</p>
                    <button type="button" onclick="startCameraStreamUstadz()"
                        class="mt-2 px-4 py-1.5 rounded-xl bg-primary text-black font-black text-xs">
                        Coba Lagi
                    </button>
                </div>
            </div>

            <!-- Kontrol Kamera -->
            <div class="flex items-center justify-between gap-2 px-1">
                <!-- Pilihan Device Kamera -->
                <div class="flex-1 max-w-[200px]">
                    <select id="cameraDeviceSelectUstadz" onchange="startCameraStreamUstadz(this.value)"
                        class="m3-input-glass !py-1.5 !text-xs !rounded-xl w-full">
                        <option value="">Kamera Utama</option>
                    </select>
                </div>

                <!-- Tombol Jepret Foto -->
                <button type="button" id="snapPhotoBtnUstadz" onclick="takeSnapshotFromCameraUstadz()"
                    class="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs flex items-center gap-2 shadow-md active:scale-95 transition-all">
                    <i class="bi bi-camera-fill text-sm"></i>
                    <span>Jepret Foto</span>
                </button>
            </div>
        </div>

        <!-- 4. PANEL C: AREA CROPPER 3x4 CM -->
        <div id="panelCropStageUstadz" class="hidden space-y-3">
            <div
                class="flex items-center justify-between bg-primary/10 dark:bg-primary-dark/15 px-3.5 py-2 rounded-xl border border-primary/20">
                <span class="text-xs font-black text-primary dark:text-primary-dark flex items-center gap-1.5">
                    <i class="bi bi-crop text-sm"></i> Atur & Potong Foto (Rasio 3x4)
                </span>
                <button type="button" onclick="cancelCropToSourceUstadz()"
                    class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i> Ganti Foto
                </button>
            </div>

            <!-- Cropper Box Canvas Container -->
            <div
                class="w-full bg-zinc-950 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 relative h-[300px] flex items-center justify-center">
                <img id="imageToCropUstadz" src="" class="max-w-full max-h-full block">
            </div>

            <!-- Toolbar Kontrol Cropper -->
            <div
                class="flex flex-wrap items-center justify-between gap-2 bg-zinc-50/80 dark:bg-zinc-900/60 p-2.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800">
                <!-- Rotate & Zoom Controls -->
                <div class="flex items-center gap-1">
                    <button type="button" onclick="rotateCropperUstadz(-90)" title="Putar Kiri 90°"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" onclick="rotateCropperUstadz(90)" title="Putar Kanan 90°"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" onclick="zoomCropperUstadz(0.1)" title="Perbesar"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button type="button" onclick="zoomCropperUstadz(-0.1)" title="Perkecil"
                        class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:text-primary active:scale-90 transition-all text-xs font-bold">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" onclick="resetCropperUstadz()" title="Reset Posisi"
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
    <canvas id="snapshotCanvasUstadz" class="hidden"></canvas>

    <!-- Modal Footer -->
    <div
        class="bg-zinc-50/80 dark:bg-black/40 border-t border-zinc-100 dark:border-zinc-800/80 px-5 py-3.5 flex items-center justify-between transition-colors duration-300">
        <div>
            @if ($ustadz->foto)
                <button type="button" onclick="hapusFotoUstadz()"
                    class="px-3.5 py-2 rounded-xl font-bold text-xs bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 transition-all flex items-center gap-1.5 active:scale-95 outline-none">
                    <i class="bi bi-trash3 text-xs"></i>
                    <span>Hapus Foto (Set Null)</span>
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" data-dismiss="modal" command="close" onclick="cleanupCameraAndCropperUstadz()"
                class="px-4 py-2 rounded-xl font-bold text-xs bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors outline-none">
                Batal
            </button>

            <button type="submit" id="btnSimpanFotoUstadz"
                class="m3-btn-primary px-6 py-2 outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="bi bi-check2-circle text-sm"></i>
                <span id="btnSimpanTextUstadz">Simpan Foto</span>
            </button>
        </div>
    </div>
</form>

<!-- SCRIPT LOGIKA KAMERA & CROPPER 3x4 USTADZ -->
<script>
    (function() {
        let currentStream = null;
        let cropperInstance = null;
        let activeSource = 'upload'; // 'upload' | 'camera'

        // 1. Ganti Tab Sumber Foto
        window.switchPhotoSourceUstadz = function(source) {
            activeSource = source;
            const tabUploadBtn = document.getElementById('tabUploadBtnUstadz');
            const tabCameraBtn = document.getElementById('tabCameraBtnUstadz');
            const panelUpload = document.getElementById('panelUploadFileUstadz');
            const panelCamera = document.getElementById('panelCameraUstadz');
            const panelCrop = document.getElementById('panelCropStageUstadz');

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
                startCameraStreamUstadz();
            }
        };

        // 2. Inisialisasi Akses Kamera / Webcam
        window.startCameraStreamUstadz = async function(deviceId = null) {
            const video = document.getElementById('cameraStreamVideoUstadz');
            const loading = document.getElementById('cameraLoadingUstadz');
            const errorMsg = document.getElementById('cameraErrorMsgUstadz');
            const deviceSelect = document.getElementById('cameraDeviceSelectUstadz');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (errorMsg) {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('cameraErrorTextUstadz').textContent =
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
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                currentStream = stream;
                if (video) {
                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();
                        if (loading) loading.classList.add('hidden');
                    };
                }

                // Populasi device list
                if (navigator.mediaDevices.enumerateDevices && deviceSelect && deviceSelect.options
                    .length <= 1) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                    if (videoDevices.length > 1) {
                        deviceSelect.innerHTML = '';
                        videoDevices.forEach((dev, idx) => {
                            const opt = document.createElement('option');
                            opt.value = dev.deviceId;
                            opt.textContent = dev.label || `Kamera ${idx + 1}`;
                            if (deviceId && dev.deviceId === deviceId) opt.selected = true;
                            deviceSelect.appendChild(opt);
                        });
                    }
                }
            } catch (err) {
                if (loading) loading.classList.add('hidden');
                if (errorMsg) {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('cameraErrorTextUstadz').textContent =
                        'Gagal membuka kamera: ' + (err.message || 'Izin ditolak.');
                }
            }
        };

        function stopCameraStream() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            const video = document.getElementById('cameraStreamVideoUstadz');
            if (video) video.srcObject = null;
        }

        // 3. Tangani Pemilihan File dari Storage
        window.handleFileChosenUstadz = function(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.type.match('image.*')) {
                    alert('Mohon pilih file gambar yang valid.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    initCropperStage(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        };

        // 4. Jepret Foto dari Webcam
        window.takeSnapshotFromCameraUstadz = function() {
            const video = document.getElementById('cameraStreamVideoUstadz');
            const canvas = document.getElementById('snapshotCanvasUstadz');
            if (!video || !canvas) return;

            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 960;
            const ctx = canvas.getContext('2d');

            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            stopCameraStream();
            initCropperStage(dataUrl);
        };

        // 5. Inisialisasi Stage Pemotong Foto (Cropper.js)
        function initCropperStage(imageSrc) {
            const panelUpload = document.getElementById('panelUploadFileUstadz');
            const panelCamera = document.getElementById('panelCameraUstadz');
            const panelCrop = document.getElementById('panelCropStageUstadz');
            const tabs = document.getElementById('sourceTabsContainerUstadz');
            const imageEl = document.getElementById('imageToCropUstadz');

            panelUpload.classList.add('hidden');
            panelCamera.classList.add('hidden');
            tabs.classList.add('hidden');
            panelCrop.classList.remove('hidden');

            imageEl.src = imageSrc;
            destroyCropper();

            setTimeout(() => {
                if (typeof Cropper !== 'undefined') {
                    cropperInstance = new Cropper(imageEl, {
                        aspectRatio: 3 / 4,
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
                    });
                }
            }, 100);
        }

        // 6. Kontrol Cropper Tools
        window.rotateCropperUstadz = function(degree) {
            if (cropperInstance) cropperInstance.rotate(degree);
        };

        window.zoomCropperUstadz = function(ratio) {
            if (cropperInstance) cropperInstance.zoom(ratio);
        };

        window.resetCropperUstadz = function() {
            if (cropperInstance) cropperInstance.reset();
        };

        window.cancelCropToSourceUstadz = function() {
            destroyCropper();
            document.getElementById('panelCropStageUstadz').classList.add('hidden');
            document.getElementById('sourceTabsContainerUstadz').classList.remove('hidden');
            window.switchPhotoSourceUstadz(activeSource);
        };

        function destroyCropper() {
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
        }

        window.cleanupCameraAndCropperUstadz = function() {
            stopCameraStream();
            destroyCropper();
        };

        // 7. Aksi Hapus Foto (Set Null)
        window.hapusFotoUstadz = function() {
            Swal.fire({
                title: 'Hapus Foto Profil?',
                text: 'Foto ustadz akan dihapus dan dikembalikan ke avatar default.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#71717a',
                confirmButtonText: 'Ya, Hapus Foto!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('ustadz.update-foto', $ustadz->id) }}',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content') ||
                                '{{ csrf_token() }}',
                            hapus_foto: 1
                        },
                        headers: {
                            Accept: 'application/json'
                        },
                        success: function(res) {
                            cleanupCameraAndCropperUstadz();
                            window.closeDialogModal();

                            if (typeof Toast !== 'undefined') {
                                Toast.fire({
                                    icon: 'success',
                                    title: res.message || 'Foto berhasil dihapus!'
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
        const form = document.getElementById('formCropUploadFotoUstadz');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (cropperInstance) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<i class="bi bi-arrow-repeat animate-spin mr-1.5"></i> Memproses...';

                    const canvas = cropperInstance.getCroppedCanvas({
                        width: 600,
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

                        const formData = new FormData();
                        formData.append('_token', $('meta[name="csrf-token"]').attr('content') ||
                            '{{ csrf_token() }}');
                        formData.append('foto', blob, 'foto_ustadz_3x4.jpg');

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
                                cleanupCameraAndCropperUstadz();
                                window.closeDialogModal();

                                if (typeof Toast !== 'undefined') {
                                    Toast.fire({
                                        icon: 'success',
                                        title: response.message ||
                                            'Foto Ustadz berhasil diperbarui!',
                                    });
                                }

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

        $(document).one('click', '[data-dismiss="modal"], el-dialog-backdrop', function() {
            cleanupCameraAndCropperUstadz();
        });
    })();
</script>
