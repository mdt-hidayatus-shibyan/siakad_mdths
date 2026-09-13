function showLoader(text = "Memproses...") {
    $("#loadingText").text(text);
    // jQuery sudah menangani animasinya (200ms) secara halus
    $("#loadingOverlay").removeClass("hidden").hide().fadeIn(200);
}

function hideLoader() {
    // jQuery menangani efek pudar secara otomatis
    $("#loadingOverlay").fadeOut(200, function () {
        $(this).addClass("hidden");
    });
}

function toggleDarkMode() {
    const html = document.documentElement;
    const iconElement = document.getElementById("iconDarkMode");

    // Jika saat ini sedang mode gelap
    if (html.classList.contains("dark")) {
        // Ubah ke mode terang
        html.classList.remove("dark");
        html.classList.add("light");
        localStorage.theme = "light"; // Simpan pilihan

        // Ubah ikon menjadi Bulan
        iconElement.classList.remove("bi-sun-fill");
        iconElement.classList.add("bi-moon-fill");
    } else {
        // Ubah ke mode gelap
        html.classList.add("dark");
        html.classList.remove("light");
        localStorage.theme = "dark"; // Simpan pilihan

        // Ubah ikon menjadi Matahari
        iconElement.classList.remove("bi-moon-fill");
        iconElement.classList.add("bi-sun-fill");
    }
}

function toggleSidebar() {
    const aside = document.getElementById("sidebarAside");
    const backdrop = document.getElementById("sidebarBackdrop");
    if (window.innerWidth >= 768) {
        aside.classList.toggle("sidebar-collapsed");
    } else {
        if (aside.classList.contains("-translate-x-full")) {
            aside.classList.remove("-translate-x-full");
            backdrop.classList.remove("hidden");
        } else {
            aside.classList.add("-translate-x-full");
            backdrop.classList.add("hidden");
        }
    }
}

function toggleDropdown(buttonElement) {
    // Cari container (div) pembungkus submenu tepat di bawah tombol yang diklik
    const submenuContent = buttonElement.nextElementSibling;
    const icon = buttonElement.querySelector(".bi-chevron-right");

    // Gunakan trik Tailwind max-h-0 (tersembunyi) dan max-h-96 (terbuka mulus)
    if (submenuContent.classList.contains("max-h-0")) {
        // Tutup semua submenu lain yang sedang terbuka
        document.querySelectorAll("nav .max-h-96").forEach((el) => {
            if (el !== submenuContent) {
                el.classList.remove("max-h-96");
                el.classList.add("max-h-0");
                el.previousElementSibling
                    .querySelector(".bi-chevron-right")
                    .classList.remove("rotate-90");
            }
        });

        // Buka yang diklik
        submenuContent.classList.remove("max-h-0");
        submenuContent.classList.add("max-h-96");
        icon.classList.add("rotate-90");
    } else {
        // Tutup
        submenuContent.classList.remove("max-h-96");
        submenuContent.classList.add("max-h-0");
        icon.classList.remove("rotate-90");
    }
}

// ==========================================
// A. KONFIGURASI TOAST (M3 Expressive Solid)
// ==========================================
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    // Gunakan Zinc-900 untuk OLED Dark Mode Elevation
    background: document.documentElement.classList.contains("dark")
        ? "#18181b"
        : "#ffffff",
    color: document.documentElement.classList.contains("dark")
        ? "#f4f4f5"
        : "#18181b",
    customClass: {
        // Compact M3 Toast dengan kelengkungan kapsul
        popup: "!rounded-2xl !p-3 border border-zinc-200 dark:border-zinc-800 shadow-lg dark:shadow-none transition-colors duration-300",
        title: "text-[13px] font-bold tracking-wide",
    },
    didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
});

// ==========================================
// A. MESIN BUKA & TUTUP MODAL (M3 Expressive)
// ==========================================

// Fungsi Helper untuk Menutup Modal
window.closeDialogModal = function () {
    if (typeof window.cleanupCameraAndCropper === "function") {
        try {
            window.cleanupCameraAndCropper();
        } catch (e) {}
    }

    const $modal = $("#modal-action");
    const $backdrop = $modal.find("el-dialog-backdrop");
    const $panel = $modal.find("el-dialog-panel");
    const $wrapper = $("#modal-content-wrapper");

    $backdrop.attr("data-closed", "");
    $panel.attr("data-closed", "");

    setTimeout(() => {
        if ($modal.length > 0 && typeof $modal[0].close === "function") {
            $modal[0].close();
        }
        $wrapper.html("");
    }, 300);
};

// Listener Tutup Modal (Tombol Batal / X)
$(document).on(
    "click",
    '[data-dismiss="modal"], [command="close"]',
    function (e) {
        e.preventDefault();
        window.closeDialogModal();
    },
);

// ==========================================
// B. FUNGSI GLOBAL PEMANGGIL MODAL
// ==========================================
// Bisa dipanggil langsung lewat JS: handleModalAction('/url-tujuan');
// Atau otomatis dari class .action-modal
window.handleModalAction = function (url) {
    const $modal = $("#modal-action");
    const $backdrop = $modal.find("el-dialog-backdrop");
    const $panel = $modal.find("el-dialog-panel");
    const $wrapper = $("#modal-content-wrapper");

    if ($modal.length === 0) {
        console.error("Elemen #modal-action tidak ditemukan di halaman!");
        return;
    }

    // Tampilan Loading UI
    $wrapper.html(
        `<div class="py-12 flex flex-col items-center justify-center">
            <div class="w-12 h-12 rounded-[1.25rem] bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/50 flex items-center justify-center mb-4 shadow-sm transition-colors duration-300">
                <i class="bi bi-arrow-repeat animate-spin text-3xl text-primary dark:text-primary-dark"></i>
            </div>
            <span class="text-[13px] font-bold tracking-wide text-zinc-900 dark:text-white animate-pulse transition-colors duration-300">Menyiapkan Formulir...</span>
        </div>`,
    );

    $modal[0].showModal();

    setTimeout(() => {
        $backdrop.removeAttr("data-closed");
        $panel.removeAttr("data-closed");
    }, 10);

    $.get(url)
        .done(function (html) {
            $wrapper.html(html);
        })
        .fail(function () {
            $wrapper.html(
                `<div class="py-12 flex flex-col items-center justify-center text-center px-4">
                    <div class="w-12 h-12 rounded-[1.25rem] bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-500/20 flex items-center justify-center mb-4 transition-colors duration-300">
                        <i class="bi bi-exclamation-triangle-fill text-2xl text-red-600 dark:text-red-400"></i>
                    </div>
                    <span class="text-base font-bold tracking-tight text-zinc-900 dark:text-white mb-1 transition-colors duration-300">Gagal memuat data</span>
                    <span class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 transition-colors duration-300">Silakan coba beberapa saat lagi.</span>
                </div>`,
            );
        });
};

// Otomatis pasang listener pada semua tombol class .action-modal
$(document).on("click", ".action-modal", function (e) {
    e.preventDefault();
    let url = $(this).attr("href") || $(this).data("url");
    if (url) {
        window.handleModalAction(url);
    }
});

// ==========================================
// C. MESIN SUBMIT FORM AJAX + TOAST
// ==========================================
$(document).on(
    "submit",
    "#modal-content-wrapper form.ajax-form, #modal-content-wrapper form.ajax-form-matriks",
    function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr("action");
        let method = form.attr("method") || "POST";
        let submitBtn = form.find('button[type="submit"]');
        let originalBtnText = submitBtn.html();

        let formData = new FormData(this);

        // DAFtAR LENGKAP JENIS INPUT UNTUK RESET & ERROR HANDLING
        const inputSelector = `
        input[type="text"], input[type="number"], input[type="email"], 
        input[type="password"], input[type="date"], input[type="time"], 
        input[type="file"], input[type="url"], input[type="tel"], 
        select, textarea
    `;

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                Accept: "application/json",
            },
            beforeSend: function () {
                submitBtn
                    .html(
                        '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Memproses...',
                    )
                    .prop("disabled", true);
                form.find(".error-ajax").remove();

                // Reset border ke normal untuk SEMUA jenis input
                let inputs = form.find(inputSelector);
                inputs
                    .removeClass(
                        "border-1 border-red-500 dark:border-red-500 focus:border-red-600 dark:focus:border-red-400 focus:ring-2 focus:ring-red-500/20",
                    )
                    .addClass(
                        "border border-transparent focus:border-primary dark:focus:border-primary-dark focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary-dark/20",
                    );
            },
            success: function (response) {
                submitBtn.html(originalBtnText).prop("disabled", false);
                window.closeDialogModal();

                // Notifikasi Sukses
                if (typeof Toast !== "undefined") {
                    Toast.fire({
                        icon: "success",
                        title: response.message || "Data berhasil disimpan!",
                    });
                } else {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: response.message || "Data berhasil disimpan.",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }

                // AUTO REFRESH DATA GRID / DATA TABLE (SEAMLESS AJAX)
                let refreshTarget =
                    form.data("refresh-target") ||
                    form.attr("data-refresh-target");

                $.get(window.location.href, function (htmlResponse) {
                    let targets = refreshTarget
                        ? [refreshTarget]
                        : ["#data-grid-container", "#data-table-container"];
                    let targetFound = false;

                    for (let target of targets) {
                        const $newContainer = $(htmlResponse).find(target);
                        if ($newContainer.length && $(target).length) {
                            $(target)
                                .hide()
                                .html($newContainer.html())
                                .attr(
                                    "data-events",
                                    $newContainer.attr("data-events"),
                                )
                                .fadeIn(300, function () {
                                    $(document).trigger("dataGridRefreshed");
                                });
                            targetFound = true;
                        }
                    }

                    if (!targetFound) {
                        location.reload();
                    }
                });
            },
            error: function (xhr) {
                submitBtn.html(originalBtnText).prop("disabled", false);

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {
                        // Coba tangkap elemen berdasarkan attribute name="..."
                        let input = form.find(`[name="${field}"]`);

                        // Jika field adalah array (contoh: name="dokumen[]"), Laravel akan memvalidasi dengan nama "dokumen.0", "dokumen.1", dst.
                        if (input.length === 0 && field.includes(".")) {
                            let arrayField = field.split(".")[0] + "[]";
                            input = form.find(`[name="${arrayField}"]`);
                        }

                        if (input.length > 0) {
                            let errorMessage = `<p class="error-ajax text-[11px] font-bold text-red-500 dark:text-red-400 mt-1.5 ml-3 flex items-center transition-colors duration-300">
                            <i class="bi bi-exclamation-circle-fill mr-1.5"></i> ${messages[0]}
                        </p>`;

                            // Terapkan border error ke SEMUA jenis input yang terdeteksi
                            if (input.is(inputSelector)) {
                                input
                                    .removeClass(
                                        "border border-transparent focus:border-primary dark:focus:border-primary-dark focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary-dark/20",
                                    )
                                    .addClass(
                                        "border-1 border-red-500 dark:border-red-500 focus:border-red-600 dark:focus:border-red-400 focus:ring-2 focus:ring-red-500/20",
                                    );
                            }

                            // Penempatan Teks Error (Dukung Radio, Checkbox, Select2 jika ada)
                            if (input.is(":radio") || input.is(":checkbox")) {
                                input
                                    .closest(".flex, .grid, label")
                                    .after(errorMessage);
                            } else if (
                                input.hasClass("select2-hidden-accessible")
                            ) {
                                // Khusus jika menggunakan plugin Select2
                                input
                                    .next(".select2-container")
                                    .after(errorMessage);
                            } else {
                                input.after(errorMessage);
                            }
                        }
                    });
                } else {
                    if (typeof Toast !== "undefined") {
                        Toast.fire({
                            icon: "error",
                            title: "Gagal terhubung ke server!",
                        });
                    } else {
                        Swal.fire(
                            "Oops!",
                            "Terjadi kesalahan pada server.",
                            "error",
                        );
                    }
                    window.closeDialogModal();
                }
            },
        });
    },
);

// ==========================================
// D. MESIN DELETE DENGAN SWEETALERT2 (Solid M3 Expressive)
// ==========================================
// ==========================================
// E. HELPER: REFRESH DATA GRID (GLOBAL)
// ==========================================
// Fungsi ini bisa dipanggil kapan saja untuk me-refresh tabel/grid tanpa reload halaman
window.refreshDataGrid = function (targetSelector = "#data-grid-container") {
    $.get(window.location.href, function (htmlResponse) {
        let activeSelector = targetSelector;
        let $newContainer = $(htmlResponse).find(activeSelector);

        if (
            !$newContainer.length &&
            activeSelector === "#data-grid-container"
        ) {
            activeSelector = "#data-table-container";
            $newContainer = $(htmlResponse).find(activeSelector);
        }

        if ($newContainer.length && $(activeSelector).length) {
            // Animasi Opacity agar aman untuk Grid & Flexbox
            $(activeSelector).animate({ opacity: 0 }, 200, function () {
                $(this)
                    .html($newContainer.html())
                    .attr("data-events", $newContainer.attr("data-events"));
                $(this).animate({ opacity: 1 }, 300, function () {
                    $(document).trigger("dataGridRefreshed");
                });
            });
        } else {
            location.reload();
        }
    });
};

// ==========================================
// F. MODULE: HAPUS DATA (SWEETALERT + AJAX)
// ==========================================
$(document).on("submit", ".delete-ajax", function (e) {
    e.preventDefault();
    const $form = $(this);

    // Ambil target refresh dinamis dari form (jika ada)
    const refreshTarget =
        $form.attr("data-refresh-target") || "#data-grid-container";

    Swal.fire({
        title: '<span class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">Apakah Anda yakin?</span>',
        html: '<p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1 transition-colors duration-300">Data ini akan dihapus permanen dan tidak dapat dikembalikan.</p>',
        icon: "warning",
        heightAuto: false,
        showCancelButton: true,
        buttonsStyling: false,
        background: document.documentElement.classList.contains("dark")
            ? "#18181b"
            : "#ffffff",
        color: document.documentElement.classList.contains("dark")
            ? "#f4f4f5"
            : "#18181b",
        customClass: {
            popup: "m3-card !rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xl dark:shadow-none p-5 md:p-6 transition-colors duration-300",
            actions: "gap-3 mt-6 flex flex-wrap justify-center",
            confirmButton:
                "min-h-[40px] min-w-[100px] rounded-2xl px-6 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-200 text-white font-bold text-[13px] shadow-sm dark:shadow-none hover:-translate-y-0.5 active:scale-95 transition-all outline-none border border-transparent dark:border-red-500/20",
            cancelButton:
                "min-h-[40px] min-w-[100px] rounded-2xl px-6 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-black dark:hover:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-bold text-[13px] border border-zinc-200 dark:border-zinc-700 hover:-translate-y-0.5 active:scale-95 transition-all outline-none",
        },
        confirmButtonText: '<i class="bi bi-trash-fill mr-1.5"></i> Hapus',
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            // Tambahan UX: Tampilkan Loading saat proses hapus berjalan
            Swal.fire({
                title: "Menghapus Data...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                background: document.documentElement.classList.contains("dark")
                    ? "#18181b"
                    : "#ffffff",
                color: document.documentElement.classList.contains("dark")
                    ? "#f4f4f5"
                    : "#18181b",
            });

            $.ajax({
                url: $form.attr("action"),
                method: $form.attr("method") || "POST", // Biasanya method spoofing laravel ada di input _method
                data: $form.serialize(),
                headers: { Accept: "application/json" },
                success: function (response) {
                    Swal.close(); // Tutup loading

                    if (typeof Toast !== "undefined") {
                        Toast.fire({
                            icon: "success",
                            title: response.message || "Data berhasil dihapus!",
                        });
                    }

                    // Logika Redirect atau Refresh Grid Dinamis
                    if (response.redirect) {
                        setTimeout(
                            () => (window.location.href = response.redirect),
                            800,
                        );
                    } else {
                        // Gunakan Helper Global
                        window.refreshDataGrid(refreshTarget);
                    }
                },
                error: function (xhr) {
                    Swal.close(); // Tutup loading
                    let errorMsg =
                        xhr.responseJSON?.message || "Gagal menghapus data!";

                    if (typeof Toast !== "undefined") {
                        Toast.fire({ icon: "error", title: errorMsg });
                    } else {
                        Swal.fire("Error!", errorMsg, "error");
                    }
                },
            });
        }
    });
});

// ==========================================
// G. MODULE: TOGGLE STATUS (OPTIMISTIC UI)
// ==========================================
$(document).on("change", ".toggle-status-ajax", function () {
    const $checkbox = $(this);
    const url = $checkbox.data("url");
    const fieldName = $checkbox.data("name") || "is_active";
    const textActive = $checkbox.data("text-active") || "Aktif";
    const textInactive = $checkbox.data("text-inactive") || "Nonaktif";
    const isChecked = $checkbox.is(":checked");

    // Ambil target refresh dinamis dari checkbox (jika ada)
    const refreshTarget =
        $checkbox.data("refresh-target") || "#data-grid-container";

    // Cari span teks di dalam label yang sama
    const $textSpan = $checkbox.closest("label").find(".status-text");

    // Optimistic UI: Ubah teks instan
    if ($textSpan.length) {
        $textSpan.text(isChecked ? textActive : textInactive);
    }

    // Siapkan payload data (Sertakan CSRF Token agar request POST/PATCH tidak 419)
    const payload = {};
    payload[fieldName] = isChecked ? 1 : 0;
    payload["_token"] = $('meta[name="csrf-token"]').attr("content"); // Penting untuk Laravel!

    $.ajax({
        url: url,
        type: "POST", // Sesuaikan jika API Anda menggunakan PATCH
        data: payload,
        headers: { Accept: "application/json" },
        success: function (response) {
            // Gunakan Helper Global
            window.refreshDataGrid(refreshTarget);

            if (typeof Toast !== "undefined") {
                Toast.fire({
                    icon: "success",
                    title: response.message || "Status berhasil diupdate!",
                });
            }
        },
        error: function (xhr) {
            // Rollback UI jika gagal
            $checkbox.prop("checked", !isChecked);
            if ($textSpan.length) {
                $textSpan.text(!isChecked ? textActive : textInactive);
            }

            let errorMsg =
                xhr.responseJSON?.message || "Gagal mengubah status!";
            if (typeof Toast !== "undefined") {
                Toast.fire({ icon: "error", title: errorMsg });
            }
        },
    });
});

// ==========================================
// H. MODULE: QUICK AJAX FORM / ACTION POST
// ==========================================
$(document).on("submit", "form.ajax-post, form.ajax-action", function (e) {
    e.preventDefault();
    const $form = $(this);
    const refreshTarget =
        $form.attr("data-refresh-target") || "#data-table-container";
    const submitBtn = $form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();

    $.ajax({
        url: $form.attr("action"),
        method: $form.attr("method") || "POST",
        data: $form.serialize(),
        headers: { Accept: "application/json" },
        beforeSend: function () {
            submitBtn.prop("disabled", true);
        },
        success: function (response) {
            submitBtn.html(originalBtnText).prop("disabled", false);
            if (typeof Toast !== "undefined") {
                Toast.fire({
                    icon: "success",
                    title: response.message || "Aksi berhasil diproses!",
                });
            }
            window.refreshDataGrid(refreshTarget);
        },
        error: function (xhr) {
            submitBtn.html(originalBtnText).prop("disabled", false);
            let errorMsg = xhr.responseJSON?.message || "Gagal memproses aksi!";
            if (typeof Toast !== "undefined") {
                Toast.fire({ icon: "error", title: errorMsg });
            } else {
                Swal.fire("Error!", errorMsg, "error");
            }
        },
    });
});

// 1. FUNGSI FULLSCREEN
function toggleFullscreen() {
    const icon = document.getElementById("iconFullscreen");

    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch((err) => {
            console.log(`Gagal mengaktifkan fullscreen: ${err.message}`);
        });
        icon.classList.replace("bi-arrows-fullscreen", "bi-fullscreen-exit");
    } else {
        document.exitFullscreen();
        icon.classList.replace("bi-fullscreen-exit", "bi-arrows-fullscreen");
    }
}

// Listener jika user menekan tombol 'ESC' di keyboard untuk keluar fullscreen
document.addEventListener("fullscreenchange", () => {
    const icon = document.getElementById("iconFullscreen");
    if (!document.fullscreenElement) {
        icon.classList.replace("bi-fullscreen-exit", "bi-arrows-fullscreen");
    }
});

function konfirmasiLogout() {
    Swal.fire({
        title: '<span class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight transition-colors duration-300">Keluar Aplikasi?</span>',
        html: '<p class="text-[13px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1 transition-colors duration-300">Anda harus login kembali untuk masuk ke sistem.</p>',
        icon: "warning",
        showCancelButton: true,
        heightAuto: false,
        confirmButtonText: "Ya, Keluar!",
        cancelButtonText: "Batal",
        buttonsStyling: false,
        // Menggunakan Elevasi OLED M3 (Zinc-900)
        background: document.documentElement.classList.contains("dark")
            ? "#18181b"
            : "#ffffff",
        color: document.documentElement.classList.contains("dark")
            ? "#f4f4f5"
            : "#18181b",
        customClass: {
            popup: "m3-card !rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xl dark:shadow-none p-5 md:p-6 transition-colors duration-300",
            actions: "gap-3 mt-6 flex flex-wrap justify-center",
            confirmButton:
                "min-h-[40px] min-w-[100px] rounded-2xl px-6 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-200 text-white font-bold text-[13px] shadow-sm transition-all outline-none border border-transparent dark:border-red-500/20 hover:-translate-y-0.5 active:scale-95",
            cancelButton:
                "min-h-[40px] min-w-[100px] rounded-2xl px-6 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-black dark:hover:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-bold text-[13px] border border-zinc-200 dark:border-zinc-700 transition-all outline-none hover:-translate-y-0.5 active:scale-95",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("form-logout").submit();
        }
    });
}

// 2. FUNGSI PENCARIAN MENU (QUICK SEARCH)
function searchMenu() {
    const input = document.getElementById("menuSearchInput");
    const filter = input.value.toLowerCase();
    const dropdown = document.getElementById("searchMenuDropdown");

    // Mengambil seluruh tag <a> yang ada di dalam Sidebar Anda
    const links = document.querySelectorAll("aside a");
    let resultsHTML = "";
    let count = 0;

    if (filter.length > 0) {
        links.forEach((link) => {
            const text = link.innerText || link.textContent;
            const url = link.getAttribute("href");

            if (
                text.toLowerCase().includes(filter) &&
                url &&
                url !== "#" &&
                !url.includes("javascript:void(0)")
            ) {
                // Menambahkan area sentuh compact min-h-[40px]
                resultsHTML += `
                        <a href="${url}" class="min-h-[40px] px-4 py-2 text-[13px] font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors flex items-center gap-3 rounded-xl mx-2 mb-1">
                            <i class="bi bi-arrow-return-right text-zinc-400 dark:text-zinc-500 text-base"></i> ${text.trim()}
                        </a>
                    `;
                count++;
            }
        });

        if (count === 0) {
            resultsHTML = `<div class="px-4 py-4 text-[13px] font-bold text-zinc-400 dark:text-zinc-500 text-center">Menu tidak ditemukan</div>`;
        }

        dropdown.innerHTML = resultsHTML;
        dropdown.classList.remove("hidden");
    } else {
        dropdown.classList.add("hidden");
    }
}

function toggleAgendaDropdown() {
    const dropdown = document.getElementById("agendaDropdown");
    dropdown.classList.toggle("hidden");
}

// Menutup Dropdown Pencarian jika user klik di luar area
document.addEventListener("click", function (event) {
    // 1. ELEMEN UNTUK PENCARIAN MENU
    const searchInput = document.getElementById("menuSearchInput");
    const searchDropdown = document.getElementById("searchMenuDropdown");

    if (searchInput && searchDropdown) {
        if (
            !searchInput.contains(event.target) &&
            !searchDropdown.contains(event.target)
        ) {
            searchDropdown.classList.add("hidden");
        }
    }

    // 2. ELEMEN UNTUK DROPDOWN AGENDA HARI INI
    const btnAgenda = document.querySelector(
        '[onclick="toggleAgendaDropdown()"]',
    );
    const agendaDropdown = document.getElementById("agendaDropdown");

    if (btnAgenda && agendaDropdown) {
        if (
            !btnAgenda.contains(event.target) &&
            !agendaDropdown.contains(event.target)
        ) {
            agendaDropdown.classList.add("hidden");
        }
    }
});

$(window).on("load", async function () {
    const iconElement = document.getElementById("iconDarkMode");
    if (iconElement) {
        if (document.documentElement.classList.contains("dark")) {
            iconElement.classList.remove("bi-moon-fill");
            iconElement.classList.add("bi-sun-fill");
        } else {
            iconElement.classList.remove("bi-sun-fill");
            iconElement.classList.add("bi-moon-fill");
        }
    }

    hideLoader();

    const hijriTextElement = document.getElementById("headerHijriText");

    if (hijriTextElement) {
        try {
            const d = new Date();
            const day = String(d.getDate()).padStart(2, "0");
            const month = String(d.getMonth() + 1).padStart(2, "0");
            const year = d.getFullYear();

            const response = await fetch(
                `https://api.aladhan.com/v1/gToH?date=${day}-${month}-${year}&adjustment=0`,
            );
            const res = await response.json();

            if (res.code === 200) {
                const hijri = res.data.hijri;
                const namaBulanH = {
                    1: "Muharram",
                    2: "Shafar",
                    3: "Rabiul Awal",
                    4: "Rabiul Tsani",
                    5: "Jumadal Awal",
                    6: "Jumadal Akhir",
                    7: "Rajab",
                    8: "Sya'ban",
                    9: "Ramadhan",
                    10: "Syawal",
                    11: "Dzul Qa'dah",
                    12: "Dzul Hijjah",
                };

                hijriTextElement.innerText = `${hijri.day} ${namaBulanH[hijri.month.number]} ${hijri.year} H`;
            }
        } catch (error) {
            hijriTextElement.innerText = "Kalender Akademik";
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    // 1. Logika untuk membuka/menutup Dropdown saat tombol diklik
    document
        .querySelectorAll("[data-dropdown-toggle]")
        .forEach(function (button) {
            button.addEventListener("click", function (event) {
                event.stopPropagation(); // Mencegah klik menembus ke document

                const targetId = this.getAttribute("data-dropdown-toggle");
                const targetMenu = document.getElementById(targetId);

                // Tutup semua dropdown lain yang sedang terbuka (Opsional agar lebih rapi)
                document
                    .querySelectorAll(".m3-dropdown-menu")
                    .forEach(function (menu) {
                        if (
                            menu.id !== targetId &&
                            !menu.classList.contains("hidden")
                        ) {
                            menu.classList.add("hidden");
                        }
                    });

                // Buka/Tutup dropdown yang diklik
                if (targetMenu) {
                    targetMenu.classList.toggle("hidden");
                }
            });
        });

    // 2. Logika untuk menutup Dropdown jika klik di sembarang tempat (luar area)
    document.addEventListener("click", function (event) {
        document.querySelectorAll(".m3-dropdown-menu").forEach(function (menu) {
            if (!menu.classList.contains("hidden")) {
                // Cek apakah area yang diklik bukan bagian dari dropdown ini
                const container = menu.closest(".dropdown-container");
                if (container && !container.contains(event.target)) {
                    menu.classList.add("hidden");
                }
            }
        });
    });
});
