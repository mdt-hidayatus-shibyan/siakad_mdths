<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>

    @include('layouts.partials.head')


    <link href="{{ asset('assets/css/custome-style.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    @stack('style')

</head>

<body
    class="text-zinc-900 dark:text-zinc-200 bg-zinc-50 dark:bg-black font-sans antialiased h-screen h-[100dvh] flex overflow-hidden transition-colors duration-300 selection:bg-primary/20 dark:selection:bg-primary-dark/30 selection:text-primary dark:selection:text-primary-dark relative">

    <!-- Ornamen Latar Belakang (Material 3 Organic Blobs) - Dikurangi opacity saat Dark Mode agar OLED tetap dominan -->
    <div
        class="fixed top-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[500px] max-h-[500px] bg-primary/10 dark:bg-primary-dark/5 rounded-2xl mix-blend-multiply dark:mix-blend-screen filter blur-[100px] opacity-60 dark:opacity-20 animate-blob pointer-events-none z-0 transition-colors duration-300">
    </div>
    <div class="fixed bottom-[-10%] right-[-5%] w-[40vw] h-[40vw] max-w-[400px] max-h-[400px] bg-primary-container/20 dark:bg-primary/5 rounded-2xl mix-blend-multiply dark:mix-blend-screen filter blur-[100px] opacity-60 dark:opacity-20 animate-blob pointer-events-none z-0 transition-colors duration-300"
        style="animation-delay: 2s;"></div>

    <!-- Overlay Loading (M3 Expressive Solid & Compact) -->
    <div id="loadingOverlay"
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center backdrop-blur-md bg-zinc-50/70 dark:bg-black/80 transition-opacity duration-300">
        <div
            class="m3-card p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl dark:shadow-none flex flex-col items-center transition-colors duration-300">
            <div class="loader mb-4 w-10 h-10 border-4"></div>
            <p class="text-primary dark:text-primary-dark font-semibold animate-pulse tracking-tight text-sm md:text-base transition-colors duration-300"
                id="loadingText">
                Memproses...
            </p>
        </div>
    </div>

    <!-- Container Utama -->
    <div id="appContainer" class="flex w-full h-full relative z-10">

        <!-- Sidebar Backdrop -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()"
            class="fixed inset-0 bg-black/40 dark:bg-black/80 z-40 hidden md:hidden backdrop-blur-sm transition-opacity duration-300">
        </div>

        @include('layouts.partials.sidebar')

        <main class="flex-1 flex flex-col h-full overflow-hidden w-full relative transition-all duration-300">

            @include('layouts.partials.header')

            <!-- View Container (Scrollable dengan extra bottom padding di mobile) -->
            <div class="flex-1 overflow-y-auto p-4 pb-24 md:p-6 md:pb-12 lg:px-8 lg:pb-12 relative custom-scrollbar scroll-smooth"
                id="viewContainer">
                {{ $slot }}
            </div>

        </main>

        <el-dialog>
            <dialog id="modal-action" aria-labelledby="dialog-title"
                class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent z-[100]">
                <!-- Backdrop -->
                <el-dialog-backdrop
                    class="fixed inset-0 bg-black/40 dark:bg-black/80 backdrop-blur-sm transition-colors duration-300 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

                <div tabindex="0"
                    class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
                    <!-- Panel Modal - OLED Support with dark:bg-zinc-900 for elevation -->
                    <el-dialog-panel id="modal-content-wrapper"
                        class="m3-card relative transform overflow-hidden w-full rounded-3xl bg-white dark:bg-zinc-900 text-left shadow-2xl dark:shadow-none border border-zinc-200 dark:border-zinc-800 transition-all duration-300 data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-lg data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>


        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('assets/js/custom-script.js') }}"></script>
        <script>
            // Penangkap Flash Session (jika ada *redirect* non-AJAX)
            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: {!! json_encode(session('success')) !!}
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: {!! json_encode(session('error')) !!}
                });
            @endif
        </script>

        @stack('script')
        @stack('scripts')
    </div>

</body>

</html>
