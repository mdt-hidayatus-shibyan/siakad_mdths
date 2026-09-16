import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/bantuan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';

class HubungiAdminScreen extends StatefulWidget {
  const HubungiAdminScreen({super.key});

  @override
  State<HubungiAdminScreen> createState() => _HubungiAdminScreenState();
}

class _HubungiAdminScreenState extends State<HubungiAdminScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _formKey = GlobalKey<FormState>();

  final _judulController = TextEditingController();
  final _deskripsiController = TextEditingController();

  String _selectedKategori = 'Laporan Kendala';

  final List<Map<String, dynamic>> _kategoriList = [
    {
      'label': 'Laporan Kendala',
      'desc': 'Masalah error, bug, atau kendala sistem',
      'icon': Icons.report_problem_rounded,
      'color': AppColors.roseDanger,
    },
    {
      'label': 'Rekomendasi Fitur',
      'desc': 'Usulan atau ide pengembangan aplikasi',
      'icon': Icons.lightbulb_rounded,
      'color': const Color(0xFF10B981),
    },
    {
      'label': 'Konsultasi & Bantuan',
      'desc': 'Pertanyaan seputar penggunaan aplikasi atau akun',
      'icon': Icons.forum_rounded,
      'color': AppColors.skyBlueAccent,
    },
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {});
      }
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<BantuanProvider>().fetchKontak();
      context.read<BantuanProvider>().fetchRiwayat();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _judulController.dispose();
    _deskripsiController.dispose();
    super.dispose();
  }

  Future<void> _launchUrlHelper(String urlStr) async {
    final uri = Uri.parse(urlStr);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(uri);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Tidak dapat membuka aplikasi terkait: $e'),
            backgroundColor: AppColors.roseDanger,
          ),
        );
      }
    }
  }

  void _kirimLaporanDanWa() async {
    if (!_formKey.currentState!.validate()) return;
    HapticHelper.medium();

    final bantuan = context.read<BantuanProvider>();

    final res = await bantuan.submitLaporan(
      kategori: _selectedKategori,
      judul: _judulController.text.trim(),
      deskripsi: _deskripsiController.text.trim(),
      tipePerangkat: 'Android App',
      versiAplikasi: '1.0.0',
    );

    if (!mounted) return;

    if (res != null) {
      HapticHelper.confirmSuccess();
      final waUrl = res['wa_url'] as String?;

      _judulController.clear();
      _deskripsiController.clear();

      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          title: Row(
            children: const [
              Icon(Icons.check_circle_rounded, color: Color(0xFF10B981)),
              SizedBox(width: 8),
              Text('Laporan Berhasil Disimpan'),
            ],
          ),
          content: const Text(
            'Laporan/Rekomendasi Anda telah tersimpan di sistem. Apakah Anda ingin langsung meneruskannya ke WhatsApp resmi Admin Madrasah?',
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                _tabController.animateTo(2); // Pindah ke tab riwayat
              },
              child: const Text('Tutup'),
            ),
            if (waUrl != null)
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF25D366),
                  foregroundColor: Colors.white,
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _launchUrlHelper(waUrl);
                  _tabController.animateTo(2);
                },
                icon: const Icon(Icons.chat_rounded, size: 16),
                label: const Text('Buka WhatsApp'),
              ),
          ],
        ),
      );
    } else {
      HapticHelper.warning();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(bantuan.errorMessage ?? 'Gagal menyimpan laporan.'),
          backgroundColor: AppColors.roseDanger,
        ),
      );
    }
  }

  void _kirimWaLangsung() {
    final user = context.read<AuthProvider>().user;
    final kontak = context.read<BantuanProvider>().kontak;
    final noWaClean = kontak?['no_wa_clean'] ?? '6281234567890';

    final text =
        "Assalamu'alaikum Admin MDTHS,\n\n"
        "Saya *${user?.name ?? 'Ustadz'}* (NIGM: ${user?.nigm ?? '-'})\n"
        "Ingin menyampaikan kendala / rekomendasi terkait aplikasi Ustadz MDTHS.\n\n"
        "Mohon bantuannya. Terima kasih.";

    final waUrl = "https://wa.me/$noWaClean?text=${Uri.encodeComponent(text)}";
    _launchUrlHelper(waUrl);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Pusat Bantuan & Hubungi Admin'),
      body: Column(
        children: [
          // Segmented Navigation Tab Bar (Konsisten dengan modul lain)
          SegmentedTabBar(
            selectedIndex: _tabController.index,
            onTabChanged: (idx) {
              _tabController.animateTo(idx);
              setState(() {});
            },
            items: const [
              SegmentedTabItem(
                activeIcon: Icons.edit_note_rounded,
                inactiveIcon: Icons.edit_note_outlined,
                label: 'Buat Laporan',
              ),
              SegmentedTabItem(
                activeIcon: Icons.support_agent_rounded,
                inactiveIcon: Icons.support_agent_outlined,
                label: 'Kontak Admin',
              ),
              SegmentedTabItem(
                activeIcon: Icons.history_rounded,
                inactiveIcon: Icons.history_outlined,
                label: 'Riwayat Tiket',
              ),
            ],
          ),

          // Content Tab Views
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildTabForm(isDark),
                _buildTabKontak(isDark),
                _buildTabRiwayat(isDark),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 1: FORM BUAT LAPORAN / KENDALA / REKOMENDASI
  // =========================================================================
  Widget _buildTabForm(bool isDark) {
    final bantuan = context.watch<BantuanProvider>();
    final user = context.watch<AuthProvider>().user;

    return RefreshIndicator(
      onRefresh: () async => bantuan.fetchKontak(),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Banner Pengantar
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color:
                        (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                            .withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.campaign_rounded,
                    size: 26,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Layanan Pengaduan & Aspirasi',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Sampaikan kendala teknis, masukan perbaikan data, atau saran fitur untuk madrasah.',
                        style: TextStyle(fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Pilih Kategori
                const Text(
                  'Pilih Kategori Laporan / Pesan',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),

                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _kategoriList.map((kat) {
                    final isSelected = _selectedKategori == kat['label'];
                    final color = kat['color'] as Color;

                    return InkWell(
                      onTap: () {
                        HapticHelper.selection();
                        setState(
                          () => _selectedKategori = kat['label'] as String,
                        );
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? color.withValues(alpha: 0.18)
                              : (isDark
                                    ? const Color(0xFF1E293B)
                                    : const Color(0xFFF1F5F9)),
                          border: Border.all(
                            color: isSelected ? color : Colors.transparent,
                            width: 1.5,
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              kat['icon'] as IconData,
                              size: 16,
                              color: isSelected
                                  ? color
                                  : (isDark ? Colors.white70 : Colors.black54),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              kat['label'] as String,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: isSelected
                                    ? FontWeight.bold
                                    : FontWeight.w500,
                                color: isSelected ? color : null,
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 16),

                // 2. Judul Singkat
                const Text(
                  'Judul Laporan / Subjek',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                TextFormField(
                  controller: _judulController,
                  decoration: InputDecoration(
                    hintText: 'Misal: Kesalahan input nilai ujian semester',
                    prefixIcon: const Icon(Icons.title_rounded),
                    filled: true,
                    fillColor: isDark ? const Color(0xFF161F16) : Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) {
                      return 'Judul laporan wajib diisi';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 14),

                // 3. Deskripsi Lengkap
                const Text(
                  'Detail Keterangan & Deskripsi',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                TextFormField(
                  controller: _deskripsiController,
                  maxLines: 5,
                  decoration: InputDecoration(
                    hintText:
                        'Jelaskan secara rinci kendala, langkah-langkah terjadinya, atau rekomendasi fitur yang diinginkan...',
                    filled: true,
                    fillColor: isDark ? const Color(0xFF161F16) : Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) {
                      return 'Deskripsi laporan wajib diisi';
                    }
                    if (val.trim().length < 10) {
                      return 'Mohon berikan deskripsi minimal 10 karakter';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 12),

                // Info Context Otomatis
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF101E17)
                        : const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isDark
                          ? const Color(0xFF1A3E2C)
                          : const Color(0xFFBFDBFE),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.info_outline_rounded,
                        size: 18,
                        color: Color(0xFF3B82F6),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Pengirim: ${user?.name ?? "Ustadz"} (${user?.kodeUstadz ?? "-"}) • Aplikasi Ustadz MDTHS v1.0.0',
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF1E40AF),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Tombol Kirim
                ElevatedButton.icon(
                  onPressed: bantuan.isSubmitting ? null : _kirimLaporanDanWa,
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    minimumSize: const Size(double.infinity, 48),
                    backgroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  icon: bantuan.isSubmitting
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.send_rounded, size: 18),
                  label: Text(
                    bantuan.isSubmitting
                        ? 'Mengirim Laporan...'
                        : 'Kirim Laporan & Teruskan ke WhatsApp',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 2: KONTAK ADMIN & INFORMASI BANTUAN
  // =========================================================================
  Widget _buildTabKontak(bool isDark) {
    final bantuan = context.watch<BantuanProvider>();
    final kontak = bantuan.kontak;

    if (bantuan.isLoadingKontak && kontak == null) {
      return const Center(child: CircularProgressIndicator());
    }

    final namaAdmin = kontak?['nama_admin'] ?? 'Administrator MDTHS';
    final noWa = kontak?['no_wa'] ?? '081234567890';
    final email = kontak?['email'] ?? 'info@mdthidayatusshibyan.sch.id';
    final alamat = kontak?['alamat'] ?? 'Somorkoneng, Kwanyar, Bangkalan';
    final jam =
        kontak?['jam_operasional'] ?? 'Sabtu - Kamis (07.00 - 17.00 WIB)';
    final faqs = (kontak?['faqs'] as List<dynamic>?) ?? [];

    return RefreshIndicator(
      onRefresh: () async => bantuan.fetchKontak(),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Kartu WhatsApp Direct
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF25D366), Color(0xFF128C7E)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF25D366).withValues(alpha: 0.3),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.chat_rounded,
                        color: Color(0xFF128C7E),
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'WhatsApp Resmi Admin',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          Text(
                            'Respon Cepat Jam Kerja',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Text(
                  'Nomor: $noWa ($namaAdmin)',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  onPressed: _kirimWaLangsung,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: const Color(0xFF128C7E),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  icon: const Icon(Icons.send_rounded, size: 16),
                  label: const Text(
                    'Chat WhatsApp Sekarang',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Detail Kontak Lainnya
          const Text(
            'Informasi Kontak & Jam Layanan',
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),

          GlassCard(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(
                    Icons.email_outlined,
                    color: AppColors.skyBlueAccent,
                  ),
                  title: const Text(
                    'Email Resmi',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  subtitle: Text(
                    email,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  trailing: IconButton(
                    icon: const Icon(Icons.open_in_new_rounded, size: 18),
                    onPressed: () => _launchUrlHelper(
                      'mailto:$email?subject=Bantuan%20Ustadz%20MDTHS',
                    ),
                  ),
                ),
                const Divider(height: 1),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(
                    Icons.access_time_rounded,
                    color: Color(0xFFF59E0B),
                  ),
                  title: const Text(
                    'Jam Operasional Layanan',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  subtitle: Text(
                    jam,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const Divider(height: 1),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(
                    Icons.location_on_outlined,
                    color: AppColors.roseDanger,
                  ),
                  title: const Text(
                    'Alamat Kantor Madrasah',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  subtitle: Text(
                    alamat,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // FAQ Singkat
          if (faqs.isNotEmpty) ...[
            const Text(
              'Pertanyaan yang Sering Diajukan (FAQ)',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            ...faqs.map(
              (f) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: GlassCard(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.help_outline_rounded,
                            size: 16,
                            color: Color(0xFF10B981),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              f['tanya'] ?? '',
                              style: const TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        f['jawab'] ?? '',
                        style: TextStyle(
                          fontSize: 11.5,
                          color: isDark
                              ? const Color(0xFFC3C8BC)
                              : const Color(0xFF475569),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 3: RIWAYAT TIKET / LAPORAN SAYA
  // =========================================================================
  Widget _buildTabRiwayat(bool isDark) {
    final bantuan = context.watch<BantuanProvider>();
    final riwayat = bantuan.riwayat;

    if (bantuan.isLoadingRiwayat && riwayat.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (riwayat.isEmpty) {
      return RefreshIndicator(
        onRefresh: () async => bantuan.fetchRiwayat(),
        child: ListView(
          padding: const EdgeInsets.all(32),
          children: [
            const SizedBox(height: 60),
            Icon(
              Icons.inbox_rounded,
              size: 64,
              color: Colors.grey.withValues(alpha: 0.5),
            ),
            const SizedBox(height: 16),
            const Center(
              child: Text(
                'Belum Ada Laporan atau Kendala',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 4),
            const Center(
              child: Text(
                'Semua laporan atau saran fitur yang Anda kirim akan tercatat di sini beserta respon dari admin.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () async => bantuan.fetchRiwayat(),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: riwayat.length,
        itemBuilder: (ctx, idx) {
          final item = riwayat[idx] as Map<String, dynamic>;
          final id = item['id'];
          final status = item['status'] ?? 'Menunggu';
          final kategori = item['kategori'] ?? 'Laporan Kendala';
          final judul = item['judul'] ?? '-';
          final deskripsi = item['deskripsi'] ?? '-';
          final responAdmin = item['respon_admin'];
          final tgl = item['created_at'] != null
              ? DateTime.tryParse(item['created_at'].toString())?.toLocal()
              : null;

          Color statusColor = const Color(0xFFF59E0B);
          if (status == 'Selesai') {
            statusColor = const Color(0xFF10B981);
          } else if (status == 'Diproses') {
            statusColor = AppColors.skyBlueAccent;
          } else if (status == 'Ditolak') {
            statusColor = AppColors.roseDanger;
          }

          return Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: GlassCard(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: statusColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: statusColor.withValues(alpha: 0.5),
                          ),
                        ),
                        child: Text(
                          status.toUpperCase(),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: statusColor,
                          ),
                        ),
                      ),
                      Text(
                        tgl != null
                            ? '${tgl.day}/${tgl.month}/${tgl.year} ${tgl.hour.toString().padLeft(2, '0')}:${tgl.minute.toString().padLeft(2, '0')}'
                            : 'Tiket #LP-$id',
                        style: const TextStyle(
                          fontSize: 11,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '[$kategori] $judul',
                    style: const TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    deskripsi,
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark
                          ? const Color(0xFFC3C8BC)
                          : const Color(0xFF475569),
                    ),
                  ),
                  if (responAdmin != null &&
                      responAdmin.toString().trim().isNotEmpty) ...[
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.3),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                Icons.admin_panel_settings_rounded,
                                size: 14,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                'Respon Admin:',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            responAdmin.toString(),
                            style: const TextStyle(fontSize: 11.5),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
