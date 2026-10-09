import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_motion.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/dashboard_model.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/dashboard_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/schedule_card.dart';
import '../../widgets/shimmer_loading.dart';
import '../../widgets/signal_indicator_widget.dart';
import '../akademik/jadwal_pelajaran_screen.dart';
import '../akademik/mata_pelajaran_screen.dart';
import '../kas/kas_ruangan_screen.dart';
import '../murid/direktori_murid_screen.dart';
import '../pelanggaran/referensi_pelanggaran_screen.dart';
import '../presensi/form_presensi_screen.dart';
import '../presensi/badal_presensi_screen.dart';
import '../tagihan/tagihan_screen.dart';
import '../tabungan/tabungan_screen.dart';
import '../akun/hubungi_admin_screen.dart';
import '../akun/pengingat_bel_screen.dart';
import '../laporan/laporan_pengampu_screen.dart';
import '../laporan/laporan_ruangan_screen.dart';
import 'kalendar_screen.dart';
import 'pengumuman_screen.dart';
import 'detail_pengumuman_screen.dart';
import '../catatan/catatan_ustadz_screen.dart';
import '../../../providers/presensi_provider.dart';
import '../presensi/checkin_ustadz_sheet.dart';
import '../panitia_imni/pembayaran_imni_screen.dart';
import '../panitia_imni/pengeluaran_imni_screen.dart';
import '../panitia_imni/presensi_imni_screen.dart';
import '../panitia_imni/nilai_imni_screen.dart';
import '../../../core/storage/storage_service.dart';
import 'widgets/customizable_menu_grid.dart';

class HomeTab extends StatefulWidget {
  final VoidCallback? onNavigateToPresensiGuru;
  final VoidCallback? onNavigateToPresensiMurid;

  const HomeTab({
    super.key,
    this.onNavigateToPresensiGuru,
    this.onNavigateToPresensiMurid,
  });

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  bool _isRefreshing = false;
  bool _isCustomizingMenu = false;
  Map<String, QuickMenuSize> _menuSizes = {};
  List<String> _pinnedMenuIds = [];

  static const List<String> _defaultMenuCepatOrder = [
    'kalender',
    'jadwal',
    'mapel',
    'pelanggaran',
    'catatan',
    'pengumuman',
    'laporan',
    'tabungan',
    'badal',
    'pengingat_bel',
  ];

  static const List<String> _defaultWaliRuanganOrder = [
    'wali_kas',
    'wali_tagihan',
    'wali_murid',
    'wali_laporan',
  ];

  static const List<String> _defaultPanitiaImniOrder = [
    'imni_tagihan',
    'imni_pengeluaran',
    'imni_presensi',
    'imni_nilai',
  ];

  List<String> _menuCepatOrder = List.from(_defaultMenuCepatOrder);
  List<String> _waliRuanganOrder = List.from(_defaultWaliRuanganOrder);
  List<String> _panitiaImniOrder = List.from(_defaultPanitiaImniOrder);

  @override
  void initState() {
    super.initState();
    _loadMenuPreferences();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DashboardProvider>().fetchDashboard();
    });
  }

  void _loadMenuPreferences() {
    final rawMap = StorageService.getMenuSizes();
    if (rawMap.isNotEmpty) {
      _menuSizes = rawMap.map(
        (key, value) => MapEntry(key, QuickMenuSizeExt.fromCode(value)),
      );
    }

    _pinnedMenuIds = StorageService.getPinnedMenus();

    final savedCepat = StorageService.getMenuOrder('cepat');
    if (savedCepat != null && savedCepat.isNotEmpty) {
      _menuCepatOrder = List.from(savedCepat);
      for (final id in _defaultMenuCepatOrder) {
        if (!_menuCepatOrder.contains(id)) _menuCepatOrder.add(id);
      }
    }

    final savedWali = StorageService.getMenuOrder('wali');
    if (savedWali != null && savedWali.isNotEmpty) {
      _waliRuanganOrder = List.from(savedWali);
      for (final id in _defaultWaliRuanganOrder) {
        if (!_waliRuanganOrder.contains(id)) _waliRuanganOrder.add(id);
      }
    }

    final savedImni = StorageService.getMenuOrder('imni');
    if (savedImni != null && savedImni.isNotEmpty) {
      _panitiaImniOrder = List.from(savedImni);
      for (final id in _defaultPanitiaImniOrder) {
        if (!_panitiaImniOrder.contains(id)) _panitiaImniOrder.add(id);
      }
    }
  }

  void _handleResizeMenu(String id, QuickMenuSize newSize) {
    setState(() {
      _menuSizes[id] = newSize;
    });
    final rawMap = _menuSizes.map((k, v) => MapEntry(k, v.code));
    StorageService.saveMenuSizes(rawMap);
  }

  void _handleTogglePin(String id) {
    HapticHelper.medium();
    setState(() {
      if (_pinnedMenuIds.contains(id)) {
        _pinnedMenuIds.remove(id);
      } else {
        _pinnedMenuIds.add(id);
      }
    });
    StorageService.savePinnedMenus(_pinnedMenuIds);
  }

  void _handleReorderPinnedMenu(String fromId, String toId) {
    setState(() {
      final oldIndex = _pinnedMenuIds.indexOf(fromId);
      final newIndex = _pinnedMenuIds.indexOf(toId);
      if (oldIndex != -1 && newIndex != -1) {
        final item = _pinnedMenuIds.removeAt(oldIndex);
        _pinnedMenuIds.insert(newIndex, item);
      }
    });
    StorageService.savePinnedMenus(_pinnedMenuIds);
  }

  void _handleReorderMenuCepat(String fromId, String toId) {
    setState(() {
      final oldIndex = _menuCepatOrder.indexOf(fromId);
      final newIndex = _menuCepatOrder.indexOf(toId);
      if (oldIndex != -1 && newIndex != -1) {
        final item = _menuCepatOrder.removeAt(oldIndex);
        _menuCepatOrder.insert(newIndex, item);
      }
    });
    StorageService.saveMenuOrder('cepat', _menuCepatOrder);
  }

  void _handleReorderWaliRuangan(String fromId, String toId) {
    setState(() {
      final oldIndex = _waliRuanganOrder.indexOf(fromId);
      final newIndex = _waliRuanganOrder.indexOf(toId);
      if (oldIndex != -1 && newIndex != -1) {
        final item = _waliRuanganOrder.removeAt(oldIndex);
        _waliRuanganOrder.insert(newIndex, item);
      }
    });
    StorageService.saveMenuOrder('wali', _waliRuanganOrder);
  }

  void _handleReorderPanitiaImni(String fromId, String toId) {
    setState(() {
      final oldIndex = _panitiaImniOrder.indexOf(fromId);
      final newIndex = _panitiaImniOrder.indexOf(toId);
      if (oldIndex != -1 && newIndex != -1) {
        final item = _panitiaImniOrder.removeAt(oldIndex);
        _panitiaImniOrder.insert(newIndex, item);
      }
    });
    StorageService.saveMenuOrder('imni', _panitiaImniOrder);
  }

  void _handleResetMenuPreferences() {
    HapticHelper.medium();
    setState(() {
      _menuSizes.clear();
      _pinnedMenuIds.clear();
      _menuCepatOrder = List.from(_defaultMenuCepatOrder);
      _waliRuanganOrder = List.from(_defaultWaliRuanganOrder);
      _panitiaImniOrder = List.from(_defaultPanitiaImniOrder);
    });
    StorageService.clearMenuSizes();
    StorageService.clearPinnedMenus();
    StorageService.clearMenuOrder();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('Tata letak, pin, dan ukuran menu dikembalikan ke default'),
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  void _toggleCustomizingMenu() {
    HapticHelper.selection();
    setState(() {
      _isCustomizingMenu = !_isCustomizingMenu;
    });
  }

  Future<void> _refreshData() async {
    if (_isRefreshing) return;
    setState(() {
      _isRefreshing = true;
    });
    HapticHelper.light();

    try {
      await Future.wait([
        context.read<DashboardProvider>().fetchDashboard(),
        context.read<AuthProvider>().fetchProfile(),
      ]);
      if (!mounted) return;
      HapticHelper.medium();
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
              SizedBox(width: 10),
              Text(
                'Data aplikasi berhasil diperbarui',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          backgroundColor: Theme.of(context).brightness == Brightness.dark
              ? AppColors.primaryDark
              : AppColors.primaryLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          duration: const Duration(seconds: 2),
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 80),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal memperbarui data: $e'),
          backgroundColor: Colors.redAccent,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 80),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isRefreshing = false;
        });
      }
    }
  }

  Future<void> _handlePresensiGuru(JadwalHariIniItem j) async {
    HapticHelper.light();
    final presensiP = context.read<PresensiProvider>();
    if (presensiP.sesiUstadzList.isEmpty) {
      await presensiP.fetchSesiUstadz();
    }
    if (!mounted) return;
    final matching = presensiP.sesiUstadzList
        .where((s) => s.jadwalId == j.id)
        .firstOrNull;
    if (matching != null) {
      await CheckinUstadzSheet.show(context, matching);
      if (!mounted) return;
      context.read<DashboardProvider>().fetchDashboard();
    } else {
      widget.onNavigateToPresensiGuru?.call();
    }
  }

  Future<void> _handleAbsenMurid(JadwalHariIniItem j) async {
    HapticHelper.light();
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FormPresensiScreen(
          jadwalId: j.id,
          mapel: j.mapel,
          ruangan: j.kelas,
          jam: j.jam,
        ),
      ),
    );
    if (!mounted) return;
    context.read<DashboardProvider>().fetchDashboard();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final isDark = theme.brightness == Brightness.dark;
    final user = context.watch<AuthProvider>().user;
    final dashboard = context.watch<DashboardProvider>();

    return SafeArea(
      bottom: false,
      child: RefreshIndicator(
        onRefresh: _refreshData,
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: EdgeInsets.fromLTRB(
            18,
            12,
            18,
            MediaQuery.of(context).padding.bottom + 12,
          ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Header Bar: Profile, Salam, & Action Buttons
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    AppAvatar(
                      name: user?.name ?? 'Ustadz',
                      imageUrl: user?.photo,
                      radius: 22,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            user?.name ?? '-',
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              letterSpacing: -0.2,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 3),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  (user?.isWaliRuangan == true &&
                                          (user?.ruanganWali?.isNotEmpty ??
                                              false))
                                      ? 'Wali Ruangan : ${user?.ruanganWali}'
                                      : 'Ustadz',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),
                    const SignalIndicatorWidget(size: 34),
                    const SizedBox(width: 6),
                    Tooltip(
                      message: 'Perbarui & Sinkronisasi Data',
                      child: M3ScaleOnPress(
                        onTap: (_isRefreshing || dashboard.isLoading)
                            ? null
                            : _refreshData,
                        pressedScale: 0.90,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Center(
                            child: _isRefreshing || dashboard.isLoading
                                ? SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                  )
                                : Icon(
                                    Icons.refresh_rounded,
                                    size: 18,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Tooltip(
                      message: 'Hubungi Admin & Pusat Bantuan',
                      child: M3ScaleOnPress(
                        onTap: () {
                          HapticHelper.light();
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => const HubungiAdminScreen(),
                            ),
                          );
                        },
                        pressedScale: 0.90,
                        borderRadius: BorderRadius.circular(14),
                        child: Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Center(
                            child: Icon(
                              Icons.support_agent_rounded,
                              size: 18,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.campaign_rounded,
                          size: 19,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Pengumuman Terkini',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                if (dashboard.isLoading)
                  const ShimmerLoadingList(count: 2, height: 85)
                else if (dashboard.dashboardData?.pengumumanList.isEmpty ??
                    true)
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 18,
                    ),
                    child: Center(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.info_outline_rounded,
                            size: 18,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'Belum ada pengumuman baru saat ini.',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark
                                  ? const Color(0xFF8D9387)
                                  : const Color(0xFF73796E),
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                else
                  ...dashboard.dashboardData!.pengumumanList.map(
                    (p) => _buildPengumumanCard(context, p, isDark),
                  ),
                const SizedBox(height: 14),

                // 2. Jadwal Mengajar Hari Ini
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.event_available_rounded,
                          size: 19,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Jadwal Mengajar Hari Ini',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                if (dashboard.isLoading)
                  const ShimmerLoadingList(count: 2)
                else if (dashboard.dashboardData?.isLiburHariIni ?? false)
                  GlassCard(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 20,
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF382305)
                                : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(
                            Icons.beach_access_rounded,
                            size: 24,
                            color: AppColors.amberAccent,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Hari Ini Libur KBM',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? Colors.white
                                      : const Color(0xFF92400E),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                dashboard
                                        .dashboardData
                                        ?.keteranganLiburHariIni ??
                                    'Kegiatan Belajar Mengajar Diliburkan',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  )
                else if (dashboard.dashboardData?.jadwalHariIniList.isEmpty ??
                    true)
                  const GlassCard(
                    padding: EdgeInsets.all(18),
                    child: Center(
                      child: Text(
                        'Tidak ada jadwal mengajar pada hari ini.',
                        style: TextStyle(fontSize: 13),
                      ),
                    ),
                  )
                else
                  ...dashboard.dashboardData!.jadwalHariIniList.map(
                    (j) => ScheduleCard(
                      item: j,
                      onPresensiGuruTap: () => _handlePresensiGuru(j),
                      onAbsenTap: () => _handleAbsenMurid(j),
                    ),
                  ),
                const SizedBox(height: 22),

                // 2.5 Menu Tersemat (Hanya tampil saat ada menu yang terpin)
                if (_pinnedMenuIds.isNotEmpty) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.push_pin_rounded,
                            size: 19,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'Menu Tersemat',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 1.5,
                            ),
                            decoration: BoxDecoration(
                              color: (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              '${_pinnedMenuIds.length}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ),
                        ],
                      ),
                      if (_isCustomizingMenu)
                        TextButton(
                          onPressed: () {
                            HapticHelper.medium();
                            setState(() {
                              _pinnedMenuIds.clear();
                            });
                            StorageService.savePinnedMenus([]);
                          },
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.roseDanger,
                            visualDensity: VisualDensity.compact,
                            padding: const EdgeInsets.symmetric(horizontal: 6),
                          ),
                          child: const Text(
                            'Lepas Semua',
                            style: TextStyle(fontSize: 12),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  CustomizableMenuGrid(
                    items: _buildPinnedMenuItems(user),
                    sizes: _menuSizes,
                    isEditing: _isCustomizingMenu,
                    pinnedIds: _pinnedMenuIds,
                    onTogglePin: _handleTogglePin,
                    onResize: _handleResizeMenu,
                    onLongPressTile: _toggleCustomizingMenu,
                    onReorder: _handleReorderPinnedMenu,
                  ),
                  const SizedBox(height: 24),
                ],

                // 3. Menu Cepat (Ustadz Umum & Wali Ruangan)
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.widgets_rounded,
                          size: 19,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'Menu Cepat',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    if (_isCustomizingMenu)
                      Row(
                        children: [
                          TextButton.icon(
                            onPressed: _handleResetMenuPreferences,
                            icon: const Icon(
                              Icons.restart_alt_rounded,
                              size: 16,
                            ),
                            label: const Text(
                              'Reset',
                              style: TextStyle(fontSize: 12),
                            ),
                            style: TextButton.styleFrom(
                              foregroundColor: AppColors.roseDanger,
                              visualDensity: VisualDensity.compact,
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),
                          FilledButton.icon(
                            onPressed: _toggleCustomizingMenu,
                            icon: const Icon(Icons.check_rounded, size: 16),
                            label: const Text(
                              'Selesai',
                              style: TextStyle(fontSize: 12),
                            ),
                            style: FilledButton.styleFrom(
                              visualDensity: VisualDensity.compact,
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                              ),
                              backgroundColor: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                        ],
                      )
                    else
                      InkWell(
                        onTap: _toggleCustomizingMenu,
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? colorScheme.surfaceContainerHigh
                                : colorScheme.surfaceContainerHighest
                                      .withValues(alpha: 0.5),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.tune_rounded,
                                size: 14,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                'Atur Menu',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                CustomizableMenuGrid(
                  items: _buildMenuCepatItems(),
                  sizes: _menuSizes,
                  isEditing: _isCustomizingMenu,
                  pinnedIds: _pinnedMenuIds,
                  onTogglePin: _handleTogglePin,
                  onResize: _handleResizeMenu,
                  onLongPressTile: _toggleCustomizingMenu,
                  onReorder: _handleReorderMenuCepat,
                ),
                const SizedBox(height: 18),

                // Menu Tambahan Khusus Wali Ruangan
                if (user?.isWaliRuangan ?? false) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.admin_panel_settings_rounded,
                            size: 19,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Wali Ruangan (${user?.ruanganWali ?? "-"})',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppColors.primaryContainerDark
                              : AppColors.primaryContainerLight,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'Akses Khusus',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  CustomizableMenuGrid(
                    items: _buildWaliRuanganItems(user),
                    sizes: _menuSizes,
                    isEditing: _isCustomizingMenu,
                    pinnedIds: _pinnedMenuIds,
                    onTogglePin: _handleTogglePin,
                    onResize: _handleResizeMenu,
                    onLongPressTile: _toggleCustomizingMenu,
                    onReorder: _handleReorderWaliRuangan,
                  ),
                  const SizedBox(height: 18),
                ],

                // Menu Tambahan Khusus Kepanitiaan IMNI
                if (user?.isPanitiaImni ?? false) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.stars_rounded,
                            size: 19,
                            color: AppColors.amberAccent,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Kepanitiaan IMNI (${user?.jabatanPanitiaImni ?? "-"})',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF382305)
                              : const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'Panitia IMNI',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: AppColors.amberAccent,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  CustomizableMenuGrid(
                    items: _buildPanitiaImniItems(),
                    sizes: _menuSizes,
                    isEditing: _isCustomizingMenu,
                    pinnedIds: _pinnedMenuIds,
                    onTogglePin: _handleTogglePin,
                    onResize: _handleResizeMenu,
                    onLongPressTile: _toggleCustomizingMenu,
                    onReorder: _handleReorderPanitiaImni,
                  ),
                  const SizedBox(height: 10),
                ],
              ],
            ),
          ),
        ),
      );
  }

  List<QuickMenuItemData> _sortItemsByOrder(
    List<QuickMenuItemData> items,
    List<String> order,
  ) {
    final itemMap = {for (final item in items) item.id: item};
    final sorted = <QuickMenuItemData>[];
    for (final id in order) {
      if (itemMap.containsKey(id)) {
        sorted.add(itemMap[id]!);
      }
    }
    for (final item in items) {
      if (!sorted.any((e) => e.id == item.id)) {
        sorted.add(item);
      }
    }
    return sorted;
  }

  List<QuickMenuItemData> _getAllMenuCepatItems() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;

    return _sortItemsByOrder([
      QuickMenuItemData(
        id: 'kalender',
        icon: Icons.calendar_month_rounded,
        title: 'Kalender',
        shortTitle: 'Kalender',
        subtitle: 'Agenda & Libur',
        accentColor: AppColors.amberAccent,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const KalendarScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'jadwal',
        icon: Icons.schedule_rounded,
        title: 'Jadwal Mengajar',
        shortTitle: 'Jadwal',
        subtitle: 'Sesi Pelajaran',
        accentColor: primaryColor,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const JadwalPelajaranScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'mapel',
        icon: Icons.menu_book_rounded,
        title: 'Mata pelajaran',
        shortTitle: 'Mapel',
        subtitle: 'Daftar Kurikulum',
        accentColor: AppColors.skyBlueAccent,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const MataPelajaranScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'pelanggaran',
        icon: Icons.gavel_rounded,
        title: 'Ref. Pelanggaran',
        shortTitle: 'Pelanggaran',
        subtitle: 'Poin Pelanggaran Murid',
        accentColor: AppColors.roseDanger,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => const ReferensiPelanggaranScreen(),
            ),
          );
        },
      ),
      QuickMenuItemData(
        id: 'catatan',
        icon: Icons.edit_note_rounded,
        title: 'Catatan Ustadz',
        shortTitle: 'Catatan',
        subtitle: 'Jurnal Harian',
        accentColor: const Color(0xFF8B5CF6),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CatatanUstadzScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'pengumuman',
        icon: Icons.campaign_rounded,
        title: 'Pengumuman',
        shortTitle: 'Info',
        subtitle: 'Informasi Madrasah',
        accentColor: const Color(0xFFF97316),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PengumumanScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'laporan',
        icon: Icons.assessment_rounded,
        title: 'Rekap Laporan',
        shortTitle: 'Laporan',
        subtitle: 'Presensi & Nilai',
        accentColor: const Color(0xFF6366F1),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const LaporanPengampuScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'tabungan',
        icon: Icons.account_balance_wallet_rounded,
        title: 'Buku Tabungan',
        shortTitle: 'Tabungan',
        subtitle: 'Tabungan Madrasah',
        accentColor: const Color(0xFF0D9488),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const TabunganScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'badal',
        icon: Icons.swap_horiz_rounded,
        title: 'Ustadz Pengganti',
        shortTitle: 'Badal',
        subtitle: 'Tukar Jadwal / Piket',
        accentColor: const Color(0xFFD946EF),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const BadalPresensiScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'pengingat_bel',
        icon: Icons.notifications_active_rounded,
        title: 'Pengingat Bel',
        shortTitle: 'Alarm Bel',
        subtitle: 'Alarm Jam KBM',
        accentColor: const Color(0xFFEAB308),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PengingatBelScreen()),
          );
        },
      ),
    ], _menuCepatOrder);
  }

  List<QuickMenuItemData> _getAllWaliRuanganItems(dynamic user) {
    return _sortItemsByOrder([
      QuickMenuItemData(
        id: 'wali_kas',
        icon: Icons.money_rounded,
        title: 'Kas Ruangan',
        shortTitle: 'Kas Ruang',
        subtitle: 'Saldo & Mutasi',
        accentColor: const Color(0xFF10B981),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const KasRuanganScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'wali_tagihan',
        icon: Icons.receipt_long_rounded,
        title: 'Tagihan',
        shortTitle: 'Tagihan',
        subtitle: 'Iuran Ruangan',
        accentColor: const Color(0xFFF43F5E),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) =>
                  TagihanScreen(initialRuanganId: user?.ruanganWaliId),
            ),
          );
        },
      ),
      QuickMenuItemData(
        id: 'wali_murid',
        icon: Icons.people_alt_rounded,
        title: 'Anggota Murid',
        shortTitle: 'Murid',
        subtitle: 'Rombongan Belajar',
        accentColor: const Color(0xFF6366F1),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) =>
                  DirektoriMuridScreen(ruanganId: user?.ruanganWaliId),
            ),
          );
        },
      ),
      QuickMenuItemData(
        id: 'wali_laporan',
        icon: Icons.admin_panel_settings_rounded,
        title: 'Laporan ${user?.ruanganWali ?? "Ruangan"}',
        shortTitle: 'Laporan',
        subtitle: 'Presensi & Nilai',
        accentColor: const Color(0xFF8B5CF6),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const LaporanRuanganScreen()),
          );
        },
      ),
    ], _waliRuanganOrder);
  }

  List<QuickMenuItemData> _getAllPanitiaImniItems() {
    return _sortItemsByOrder([
      QuickMenuItemData(
        id: 'imni_tagihan',
        icon: Icons.payments_rounded,
        title: 'Tagihan IMNI',
        shortTitle: 'Tagihan',
        subtitle: 'Iuran Santri',
        accentColor: AppColors.amberAccent,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PembayaranImniScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'imni_pengeluaran',
        icon: Icons.shopping_cart_checkout_rounded,
        title: 'Pengeluaran',
        shortTitle: 'Belanja',
        subtitle: 'Arus Kas Keluar',
        accentColor: AppColors.roseDanger,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PengeluaranImniScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'imni_presensi',
        icon: Icons.fact_check_rounded,
        title: 'Presensi IMNI',
        shortTitle: 'Presensi',
        subtitle: 'Absensi Acara',
        accentColor: AppColors.skyBlueAccent,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const PresensiImniScreen()),
          );
        },
      ),
      QuickMenuItemData(
        id: 'imni_nilai',
        icon: Icons.grade_rounded,
        title: 'Nilai & Leger',
        shortTitle: 'Nilai Leger',
        subtitle: 'Rekapitulasi Nilai',
        accentColor: const Color(0xFF10B981),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const NilaiImniScreen()),
          );
        },
      ),
    ], _panitiaImniOrder);
  }

  List<QuickMenuItemData> _buildPinnedMenuItems(dynamic user) {
    final all = <QuickMenuItemData>[
      ..._getAllMenuCepatItems(),
      if (user?.isWaliRuangan ?? false) ..._getAllWaliRuanganItems(user),
      if (user?.isPanitiaImni ?? false) ..._getAllPanitiaImniItems(),
    ];
    final map = {for (final item in all) item.id: item};
    return _pinnedMenuIds
        .where((id) => map.containsKey(id))
        .map((id) => map[id]!)
        .toList();
  }

  List<QuickMenuItemData> _buildMenuCepatItems() {
    // Saring item yang sedang tersemat di atas agar tidak tampil dobel
    return _getAllMenuCepatItems()
        .where((item) => !_pinnedMenuIds.contains(item.id))
        .toList();
  }

  List<QuickMenuItemData> _buildWaliRuanganItems(dynamic user) {
    // Saring item yang sedang tersemat di atas agar tidak tampil dobel
    return _getAllWaliRuanganItems(user)
        .where((item) => !_pinnedMenuIds.contains(item.id))
        .toList();
  }

  List<QuickMenuItemData> _buildPanitiaImniItems() {
    // Saring item yang sedang tersemat di atas agar tidak tampil dobel
    return _getAllPanitiaImniItems()
        .where((item) => !_pinnedMenuIds.contains(item.id))
        .toList();
  }

  Widget _buildPengumumanCard(
    BuildContext context,
    PengumumanItem p,
    bool isDark,
  ) {
    final isPenting = p.tipe.toLowerCase() == 'penting';
    final isKegiatan = p.tipe.toLowerCase() == 'kegiatan';
    final isLibur = p.tipe.toLowerCase() == 'libur';

    final Color badgeBg;
    final Color badgeText;
    final IconData badgeIcon;

    if (isPenting) {
      badgeBg = isDark ? const Color(0xFF3B1212) : const Color(0xFFFEE2E2);
      badgeText = AppColors.roseDanger;
      badgeIcon = Icons.error_outline_rounded;
    } else if (isKegiatan) {
      badgeBg = isDark ? const Color(0xFF0F2313) : const Color(0xFFD1FAE5);
      badgeText = isDark ? AppColors.primaryDark : const Color(0xFF059669);
      badgeIcon = Icons.event_available_rounded;
    } else if (isLibur) {
      badgeBg = isDark ? const Color(0xFF382305) : const Color(0xFFFEF3C7);
      badgeText = AppColors.amberAccent;
      badgeIcon = Icons.beach_access_rounded;
    } else {
      badgeBg = isDark ? const Color(0xFF0C243B) : const Color(0xFFE0F2FE);
      badgeText = isDark ? AppColors.skyBlueAccent : const Color(0xFF0284C7);
      badgeIcon = Icons.info_outline_rounded;
    }

    final hasPdf = p.lampiranPdfUrl != null && p.lampiranPdfUrl!.isNotEmpty;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      onTap: () {
        HapticHelper.light();
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => DetailPengumumanScreen(pengumuman: p),
          ),
        );
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: badgeBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(badgeIcon, size: 12, color: badgeText),
                        const SizedBox(width: 4),
                        Text(
                          p.tipe,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: badgeText,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (hasPdf) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF3B1212)
                            : const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppColors.roseDanger.withValues(alpha: 0.3),
                        ),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.picture_as_pdf_rounded,
                            size: 11,
                            color: AppColors.roseDanger,
                          ),
                          SizedBox(width: 3),
                          Text(
                            'PDF',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
              Text(
                p.tanggalMulai,
                style: TextStyle(
                  fontSize: 11,
                  color: isDark
                      ? const Color(0xFF8D9387)
                      : const Color(0xFF73796E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            p.judul,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            p.konten,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 12,
              height: 1.4,
              color: isDark ? const Color(0xFFCCCCCC) : const Color(0xFF4B5563),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                'Baca Selengkapnya',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                ),
              ),
              const SizedBox(width: 2),
              Icon(
                Icons.chevron_right_rounded,
                size: 16,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ],
          ),
        ],
      ),
    );
  }
}
