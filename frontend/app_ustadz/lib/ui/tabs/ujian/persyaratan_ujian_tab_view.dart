import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/syarat_ujian_model.dart';
import '../../../providers/syarat_ujian_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

class PersyaratanUjianTabView extends StatefulWidget {
  const PersyaratanUjianTabView({super.key});

  @override
  State<PersyaratanUjianTabView> createState() =>
      _PersyaratanUjianTabViewState();
}

class _PersyaratanUjianTabViewState extends State<PersyaratanUjianTabView> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SyaratUjianProvider>().fetchData();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _showBeriDispensasiDialog(MuridSyaratUjianItem murid) {
    HapticHelper.light();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;
    final reasonCtrl = TextEditingController(
      text: 'Dispensasi Ujian dari Wali Ruangan',
    );
    final provider = context.read<SyaratUjianProvider>();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: primaryColor.withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.verified_user_rounded,
                color: primaryColor,
                size: 22,
              ),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Beri Dispensasi Ujian',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Beri izin/dispensasi ujian untuk santri binaan:',
              style: TextStyle(
                fontSize: 12,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '${murid.nama} (${murid.nism})',
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            if (murid.lockReason != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color:
                      (isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight)
                          .withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color:
                        (isDark
                                ? AppColors.alphaTextDark
                                : AppColors.alphaTextLight)
                            .withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.lock_outline_rounded,
                      size: 14,
                      color: isDark
                          ? AppColors.alphaTextDark
                          : AppColors.alphaTextLight,
                    ),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        murid.lockReason!,
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? AppColors.alphaTextDark
                              : AppColors.alphaTextLight,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 14),
            const Text(
              'Catatan / Alasan Dispensasi:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: reasonCtrl,
              decoration: InputDecoration(
                hintText: 'Masukkan alasan pemberian dispensasi ujian...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 10,
                ),
              ),
              maxLines: 2,
              style: const TextStyle(fontSize: 13),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await provider.beriDispensasi(
                muridId: murid.muridId,
                alasanIzin: reasonCtrl.text.trim().isEmpty
                    ? 'Dispensasi Ujian dari Wali Ruangan'
                    : reasonCtrl.text.trim(),
              );

              if (!mounted) return;
              if (success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      'Dispensasi berhasil diberikan untuk ${murid.nama}. Akses input nilai santri telah terbuka.',
                    ),
                    backgroundColor: primaryColor,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      provider.errorMessage ?? 'Gagal memberikan dispensasi.',
                    ),
                    backgroundColor: AppColors.roseDanger,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: primaryColor,
              foregroundColor: onPrimaryColor,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Beri Dispensasi'),
          ),
        ],
      ),
    );
  }

  void _showBatalkanDispensasiDialog(MuridSyaratUjianItem murid) {
    HapticHelper.light();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final provider = context.read<SyaratUjianProvider>();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: (isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight)
                    .withValues(alpha: 0.5),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.cancel_outlined,
                color: isDark
                    ? AppColors.alphaTextDark
                    : AppColors.alphaTextLight,
                size: 22,
              ),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Batalkan Dispensasi?',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin membatalkan dispensasi ujian untuk santri ${murid.nama}? Status santri akan kembali terkunci jika masih memiliki kendala administrasi.',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tutup'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await provider.batalkanDispensasi(
                muridId: murid.muridId,
              );

              if (!mounted) return;
              if (success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      'Dispensasi untuk ${murid.nama} berhasil dibatalkan.',
                    ),
                    backgroundColor: primaryColor,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      provider.errorMessage ?? 'Gagal membatalkan dispensasi.',
                    ),
                    backgroundColor: AppColors.roseDanger,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark
                  ? AppColors.alphaTextDark
                  : AppColors.alphaTextLight,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Batalkan Dispensasi'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final onPrimaryColor = isDark
        ? AppColors.onPrimaryDark
        : AppColors.onPrimaryLight;
    final provider = context.watch<SyaratUjianProvider>();

    if (provider.isLoading && provider.data == null) {
      return const Padding(
        padding: EdgeInsets.all(16),
        child: ShimmerLoadingList(count: 6, height: 76),
      );
    }

    if (provider.errorMessage != null && provider.data == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 48,
                color: AppColors.roseDanger,
              ),
              const SizedBox(height: 12),
              Text(
                provider.errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => provider.fetchData(),
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: onPrimaryColor,
                ),
              ),
            ],
          ),
        ),
      );
    }

    final muridList = provider.filteredMuridList;

    return RefreshIndicator(
      onRefresh: () => provider.fetchData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 140),
        children: [
          // ===================================================================
          // 1. FILTER RUANGAN KELAS & AGENDA UJIAN
          // ===================================================================
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.filter_list_rounded,
                      size: 18,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Pilih Ruangan Kelas & Agenda Ujian',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Dropdown Ruangan Kelas
                DropdownButtonFormField<int>(
                  key: ValueKey('ruangan_${provider.selectedRuanganId}'),
                  initialValue: provider.selectedRuanganId,
                  decoration: const InputDecoration(
                    labelText: 'Ruangan Kelas',
                    prefixIcon: Icon(Icons.meeting_room_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: provider.daftarRuangan.map((r) {
                    return DropdownMenuItem<int>(
                      value: r.id,
                      child: Text(
                        '${r.namaRuangan} (${r.namaLevel})',
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      HapticHelper.light();
                      provider.selectRuangan(val);
                    }
                  },
                ),
                const SizedBox(height: 12),

                // Dropdown Agenda Ujian
                DropdownButtonFormField<int>(
                  key: ValueKey(
                    'ujian_${provider.selectedRuanganId}_${provider.selectedUjianId}',
                  ),
                  initialValue: provider.selectedUjianId,
                  decoration: const InputDecoration(
                    labelText: 'Agenda Ujian',
                    prefixIcon: Icon(Icons.auto_stories_rounded, size: 18),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                  ),
                  items: provider.daftarUjian.map((u) {
                    return DropdownMenuItem<int>(
                      value: u.id,
                      child: Text(
                        '${u.namaUjian} (${u.semester})',
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      HapticHelper.light();
                      provider.selectUjian(val);
                    }
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ===================================================================
          // 2. KETERANGAN STATUS RUANGAN & HAK AKSES USTADZ
          // ===================================================================
          if (provider.selectedRuanganId != null &&
              provider.selectedRuanganNama.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: provider.isWaliRuangan
                    ? primaryColor.withValues(alpha: isDark ? 0.15 : 0.08)
                    : AppColors.skyBlueAccent.withValues(
                        alpha: isDark ? 0.15 : 0.08,
                      ),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: provider.isWaliRuangan
                      ? primaryColor.withValues(alpha: 0.3)
                      : AppColors.skyBlueAccent.withValues(alpha: 0.3),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(
                    provider.isWaliRuangan
                        ? Icons.verified_user_rounded
                        : Icons.info_outline_rounded,
                    size: 18,
                    color: provider.isWaliRuangan
                        ? primaryColor
                        : AppColors.skyBlueAccent,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                provider.isWaliRuangan
                                    ? 'Wali Ruangan: ${provider.selectedRuanganNama}'
                                    : 'Ruangan: ${provider.selectedRuanganNama}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: provider.isWaliRuangan
                                      ? primaryColor
                                      : AppColors.skyBlueAccent,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: provider.isWaliRuangan
                                    ? AppColors.amberAccent.withValues(
                                        alpha: 0.2,
                                      )
                                    : AppColors.skyBlueAccent.withValues(
                                        alpha: 0.2,
                                      ),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                provider.isWaliRuangan
                                    ? '⭐ Wali Ruangan'
                                    : '📘 Pengajar',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: provider.isWaliRuangan
                                      ? AppColors.amberAccent
                                      : AppColors.skyBlueAccent,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          provider.isWaliRuangan
                              ? 'Anda adalah Wali Ruangan kelas ini. Anda memiliki hak penuh untuk memberikan atau membatalkan dispensasi ujian bagi santri.'
                              : 'Wali Ruangan: ${provider.waliRuanganNama}. Pemberian izin/dispensasi ujian santri dikelola oleh Wali Ruangan.',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF555555),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],

          // ===================================================================
          // 3. STATISTIK RINGKASAN PERSYARATAN UJIAN
          // ===================================================================
          Row(
            children: [
              Expanded(
                child: _buildStatMiniCard(
                  label: 'Lunas',
                  count: provider.summary.lunas,
                  color: isDark
                      ? AppColors.hadirTextDark
                      : AppColors.hadirTextLight,
                  icon: Icons.check_circle_rounded,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildStatMiniCard(
                  label: 'Dispensasi',
                  count: provider.summary.dispensasi,
                  color: isDark
                      ? AppColors.dispensasiTextDark
                      : AppColors.dispensasiTextLight,
                  icon: Icons.verified_user_rounded,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildStatMiniCard(
                  label: 'Terkunci',
                  count: provider.summary.terkunci,
                  color: isDark
                      ? AppColors.alphaTextDark
                      : AppColors.alphaTextLight,
                  icon: Icons.lock_rounded,
                  isDark: isDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // ===================================================================
          // 4. SEARCH BAR & FILTER CHIP
          // ===================================================================
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'Cari nama atau NISM santri...',
              prefixIcon: const Icon(Icons.search_rounded, size: 18),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 16),
                      onPressed: () {
                        _searchController.clear();
                        provider.setSearchQuery('');
                      },
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 10,
              ),
            ),
            style: const TextStyle(fontSize: 13),
            onChanged: (val) => provider.setSearchQuery(val),
          ),
          const SizedBox(height: 10),

          // Filter Chips Status Syarat
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterChip(
                  'Semua',
                  provider.summary.total,
                  provider,
                  primaryColor,
                  isDark,
                ),
                _buildFilterChip(
                  'Lunas',
                  provider.summary.lunas,
                  provider,
                  isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight,
                  isDark,
                ),
                _buildFilterChip(
                  'Dispensasi',
                  provider.summary.dispensasi,
                  provider,
                  isDark
                      ? AppColors.dispensasiTextDark
                      : AppColors.dispensasiTextLight,
                  isDark,
                ),
                _buildFilterChip(
                  'Terkunci',
                  provider.summary.terkunci,
                  provider,
                  isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight,
                  isDark,
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ===================================================================
          // 5. DAFTAR SANTRI PERSYARATAN UJIAN
          // ===================================================================
          if (muridList.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 32),
              child: Center(
                child: Text(
                  provider.searchQuery.isNotEmpty ||
                          provider.filterStatus != 'Semua'
                      ? 'Tidak ada santri yang cocok dengan filter.'
                      : 'Tidak ada data santri ditemukan.',
                  style: TextStyle(
                    fontSize: 13,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
              ),
            )
          else
            ...muridList.map((m) {
              Color statusBgColor;
              Color statusTextColor;
              IconData statusIcon;

              if (m.hasDispensasi) {
                statusBgColor = isDark
                    ? AppColors.dispensasiBgDark
                    : AppColors.dispensasiBgLight;
                statusTextColor = isDark
                    ? AppColors.dispensasiTextDark
                    : AppColors.dispensasiTextLight;
                statusIcon = Icons.verified_user_rounded;
              } else if (m.isLocked) {
                statusBgColor = isDark
                    ? AppColors.alphaBgDark
                    : AppColors.alphaBgLight;
                statusTextColor = isDark
                    ? AppColors.alphaTextDark
                    : AppColors.alphaTextLight;
                statusIcon = Icons.lock_rounded;
              } else {
                statusBgColor = isDark
                    ? AppColors.hadirBgDark
                    : AppColors.hadirBgLight;
                statusTextColor = isDark
                    ? AppColors.hadirTextDark
                    : AppColors.hadirTextLight;
                statusIcon = Icons.check_circle_rounded;
              }

              final avatarInitial = m.nama.trim().isNotEmpty
                  ? m.nama.trim()[0].toUpperCase()
                  : 'S';

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: statusBgColor,
                          child: Text(
                            avatarInitial,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: statusTextColor,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                m.nama,
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'NISM: ${m.nism} • ${m.jenisKelamin == 'L' ? 'Santri Putra' : 'Santri Putri'}',
                                style: TextStyle(
                                  fontSize: 10,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: statusBgColor,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: statusTextColor.withValues(alpha: 0.3),
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                statusIcon,
                                size: 11,
                                color: statusTextColor,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                m.statusSyarat,
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: statusTextColor,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    // Keterangan Rincian Status / Alasan
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: (isDark ? Colors.white : Colors.black)
                            .withValues(alpha: 0.03),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            m.isLocked
                                ? Icons.info_outline_rounded
                                : (m.hasDispensasi
                                      ? Icons.verified_user_outlined
                                      : Icons.check_circle_outline_rounded),
                            size: 13,
                            color: statusTextColor,
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              m.hasDispensasi
                                  ? 'Dispensasi: ${m.alasanDispensasi ?? "Dispensasi Wali Ruangan"} • Oleh ${m.dispensasiOleh ?? "Wali Ruangan"}'
                                  : (m.isLocked
                                        ? 'Kendala: ${m.lockReason ?? "Menunggak Administrasi"}'
                                        : 'Status: Bebas tunggakan, syarat ujian terpenuhi.'),
                              style: TextStyle(
                                fontSize: 11,
                                color: m.isLocked
                                    ? (isDark
                                          ? AppColors.alphaTextDark
                                          : AppColors.alphaTextLight)
                                    : (m.hasDispensasi
                                          ? (isDark
                                                ? AppColors.dispensasiTextDark
                                                : AppColors.dispensasiTextLight)
                                          : (isDark
                                                ? const Color(0xFF8D9387)
                                                : const Color(0xFF73796E))),
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Tombol Aksi Dispensasi (Khusus Wali Ruangan)
                    if (provider.isWaliRuangan) ...[
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          if (m.hasDispensasi)
                            OutlinedButton.icon(
                              onPressed: () => _showBatalkanDispensasiDialog(m),
                              icon: const Icon(Icons.cancel_outlined, size: 14),
                              label: const Text(
                                'Batalkan Dispensasi',
                                style: TextStyle(fontSize: 11),
                              ),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: isDark
                                    ? AppColors.alphaTextDark
                                    : AppColors.alphaTextLight,
                                side: BorderSide(
                                  color: isDark
                                      ? AppColors.alphaTextDark
                                      : AppColors.alphaTextLight,
                                ),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                visualDensity: VisualDensity.compact,
                              ),
                            )
                          else if (m.isLocked)
                            ElevatedButton.icon(
                              onPressed: () => _showBeriDispensasiDialog(m),
                              icon: const Icon(
                                Icons.verified_user_rounded,
                                size: 14,
                              ),
                              label: const Text(
                                'Beri Dispensasi',
                                style: TextStyle(fontSize: 11),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: primaryColor,
                                foregroundColor: onPrimaryColor,
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 4,
                                ),
                                visualDensity: VisualDensity.compact,
                              ),
                            ),
                        ],
                      ),
                    ] else if (m.isLocked) ...[
                      const SizedBox(height: 6),
                      Text(
                        'Pemberian izin/dispensasi dikelola oleh Wali Ruangan (${provider.waliRuanganNama}).',
                        style: TextStyle(
                          fontSize: 10,
                          fontStyle: FontStyle.italic,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildStatMiniCard({
    required String label,
    required int count,
    required Color color,
    required IconData icon,
    required bool isDark,
  }) {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 14, color: color),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            count.toString(),
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(
    String label,
    int count,
    SyaratUjianProvider provider,
    Color chipColor,
    bool isDark,
  ) {
    final isSelected = provider.filterStatus == label;

    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text('$label ($count)', style: const TextStyle(fontSize: 11)),
        selected: isSelected,
        selectedColor: chipColor.withValues(alpha: 0.2),
        labelStyle: TextStyle(
          fontSize: 11,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? chipColor : null,
        ),
        visualDensity: VisualDensity.compact,
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 0),
        side: BorderSide(
          color: isSelected
              ? chipColor
              : (isDark ? AppColors.outlineDark : AppColors.outlineLight),
        ),
        onSelected: (_) => provider.setFilterStatus(label),
      ),
    );
  }
}
