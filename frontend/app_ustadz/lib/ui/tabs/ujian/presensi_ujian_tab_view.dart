import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/presensi_ujian_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/status_presensi_chip.dart';

class PresensiUjianTabView extends StatefulWidget {
  const PresensiUjianTabView({super.key});

  @override
  State<PresensiUjianTabView> createState() => _PresensiUjianTabViewState();
}

class _PresensiUjianTabViewState extends State<PresensiUjianTabView> {
  final TextEditingController _beritaAcaraController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final p = context.read<PresensiUjianProvider>();
      p.fetchData().then((_) {
        if (mounted && p.pengawas?.catatanBeritaAcara != null) {
          _beritaAcaraController.text = p.pengawas!.catatanBeritaAcara!;
        }
      });
    });
  }

  @override
  void dispose() {
    _beritaAcaraController.dispose();
    super.dispose();
  }

  void _showCatatanDialog(
    int muridId,
    String namaMurid,
    String? existingCatatan,
  ) {
    HapticHelper.light();
    final noteCtrl = TextEditingController(text: existingCatatan);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(
          'Catatan Presensi: $namaMurid',
          style: const TextStyle(fontSize: 15),
        ),
        content: TextField(
          controller: noteCtrl,
          decoration: const InputDecoration(
            hintText: 'Contoh: Izin terlambat 15 menit karena hujan...',
            border: OutlineInputBorder(),
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.read<PresensiUjianProvider>().updateMuridCatatan(
                muridId,
                noteCtrl.text.trim().isEmpty ? null : noteCtrl.text.trim(),
              );
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PresensiUjianProvider>();

    if (provider.isLoading && provider.data == null) {
      return const Center(child: CircularProgressIndicator());
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
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => provider.fetchData(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 140),
        children: [
          // ===================================================================
          // 1. FILTER RUANGAN KELAS (UTAMA), AGENDA UJIAN & MATA PELAJARAN
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
                      color: AppColors.primaryLight,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Pilih Ruangan, Agenda & Mata Pelajaran',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // 1.1 Dropdown Ruangan Kelas (PILIHAN PERTAMA)
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

                // 1.2 Dropdown Agenda Ujian (FILTER OTOMATIS: IMDA 1 & IMNI vs IMDA 1 & IMDA 2)
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

                // 1.3 Dropdown Mata Pelajaran Ujian
                if (provider.jadwalList.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    key: ValueKey(
                      'jadwal_${provider.selectedRuanganId}_${provider.selectedUjianId}_${provider.selectedJadwalId}',
                    ),
                    initialValue:
                        provider.jadwalList.any(
                          (j) => j.id == provider.selectedJadwalId,
                        )
                        ? provider.selectedJadwalId
                        : (provider.jadwalList.isNotEmpty
                              ? provider.jadwalList.first.id
                              : null),
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Mata Pelajaran Ujian',
                      prefixIcon: Icon(Icons.quiz_rounded, size: 18),
                      contentPadding: EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                    ),
                    items: provider.jadwalList.map((j) {
                      final dateStr =
                          j.hariTanggalSingkat ?? j.tanggalUjian ?? '-';
                      final timeStr = '${j.waktuMulai} - ${j.waktuSelesai}';
                      return DropdownMenuItem<int>(
                        value: j.id,
                        child: Text(
                          '${j.namaMapel} ($dateStr • $timeStr)',
                          style: const TextStyle(fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) {
                        HapticHelper.light();
                        provider.selectJadwal(val);
                      }
                    },
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 14),

          // ===================================================================
          // 1.5. CARD KETERANGAN RUANGAN & STATUS HAK AKSES USTADZ
          // ===================================================================
          if (provider.selectedRuanganId != null &&
              provider.selectedRuanganNama.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: provider.isWaliRuangan
                    ? AppColors.primaryLight.withValues(
                        alpha: isDark ? 0.15 : 0.08,
                      )
                    : AppColors.skyBlueAccent.withValues(
                        alpha: isDark ? 0.15 : 0.08,
                      ),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: provider.isWaliRuangan
                      ? AppColors.primaryLight.withValues(alpha: 0.3)
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
                        ? AppColors.primaryLight
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
                                      ? (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
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
                                    : '📘 Pengajar / Pengawas',
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
                              ? 'Anda adalah Wali Ruangan kelas ${provider.selectedRuanganNama}. Menampilkan seluruh mata pelajaran ujian (${provider.jadwalList.length} mapel).'
                              : 'Wali Ruangan: ${provider.waliRuanganNama}. Menampilkan ${provider.jadwalList.length} mata pelajaran yang Anda ampu / awasi di kelas ini.',
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
          // 2. KONDISI: JIKA JADWAL BELUM DIBUAT OLEH ADMINISTRATOR
          // ===================================================================
          if (provider.daftarUjian.isEmpty)
            _buildBelumAdaUjianEmptyState(context, isDark, provider)
          else if (provider.jadwalList.isEmpty)
            _buildBelumAdaJadwalEmptyState(context, isDark, provider)
          else ...[
            // ===================================================================
            // 2.5. CARD KETERANGAN MATA PELAJARAN UJIAN YANG SEDANG DIPRESENSI
            // ===================================================================
            if (provider.currentJadwal != null) ...[
              GlassCard(
                padding: const EdgeInsets.all(14),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        Icons.quiz_rounded,
                        size: 22,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            provider.currentJadwal!.namaMapel,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Row(
                            children: [
                              const Icon(
                                Icons.event_note_rounded,
                                size: 12,
                                color: Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                '${provider.currentJadwal!.hariTanggalSingkat ?? provider.currentJadwal!.tanggalUjian ?? "-"} • ${provider.currentJadwal!.waktuMulai} - ${provider.currentJadwal!.waktuSelesai}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Row(
                            children: [
                              const Icon(
                                Icons.badge_outlined,
                                size: 12,
                                color: Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  'Pengawas: ${provider.currentJadwal!.pengawasNama ?? provider.pengawas?.ustadzNama ?? "Belum Ditentukan"}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E),
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
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
            // 3. CARD PENGAWAS UJIAN & BERITA ACARA
            // ===================================================================
            if (provider.pengawas != null) ...[
              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(7),
                          decoration: BoxDecoration(
                            color: AppColors.violetAccent.withValues(
                              alpha: 0.15,
                            ),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.person_pin_rounded,
                            size: 16,
                            color: AppColors.violetAccent,
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Pengawas Ruangan & Berita Acara',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Info Pengawas Utama
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: (isDark ? Colors.white : Colors.black)
                            .withValues(alpha: 0.04),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.badge_outlined, size: 15),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Pengawas Terjadwal: ${provider.pengawas!.ustadzNama}',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),

                    // Pilihan Status Kehadiran Pengawas (H, I, S, B)
                    Row(
                      children: [
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'H',
                            label: 'Hadir',
                            isSelected: provider.pengawas!.status == 'Hadir',
                            onTap: () {
                              HapticHelper.light();
                              provider.updatePengawasStatus('Hadir');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'I',
                            label: 'Izin',
                            isSelected: provider.pengawas!.status == 'Izin',
                            onTap: () {
                              HapticHelper.light();
                              provider.updatePengawasStatus('Izin');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'S',
                            label: 'Sakit',
                            isSelected: provider.pengawas!.status == 'Sakit',
                            onTap: () {
                              HapticHelper.light();
                              provider.updatePengawasStatus('Sakit');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'B',
                            label: 'Badal',
                            isSelected: provider.pengawas!.status == 'Badal',
                            onTap: () {
                              HapticHelper.light();
                              provider.updatePengawasStatus('Badal');
                            },
                          ),
                        ),
                      ],
                    ),

                    // Dropdown Ustadz Badal jika status Badal
                    if (provider.pengawas!.status == 'Badal') ...[
                      const SizedBox(height: 10),
                      DropdownButtonFormField<int>(
                        initialValue: provider.pengawas!.ustadzPenggantiId,
                        decoration: const InputDecoration(
                          labelText: 'Pilih Ustadz Pengganti (Badal)',
                          prefixIcon: Icon(Icons.swap_horiz_rounded, size: 18),
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        items: provider.daftarBadal.map((u) {
                          return DropdownMenuItem<int>(
                            value: u.id,
                            child: Text(
                              u.nama,
                              style: const TextStyle(fontSize: 12),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            final selected = provider.daftarBadal.firstWhere(
                              (b) => b.id == val,
                            );
                            provider.updatePengawasPengganti(
                              val,
                              selected.nama,
                            );
                          }
                        },
                      ),
                    ],

                    const SizedBox(height: 10),
                    // Input Catatan / Berita Acara Ujian
                    TextField(
                      controller: _beritaAcaraController,
                      decoration: const InputDecoration(
                        hintText:
                            'Catatan Berita Acara (misal: Ujian tertib, tidak ada kendala)',
                        labelText: 'Berita Acara Singkat',
                        prefixIcon: Icon(Icons.notes_rounded, size: 16),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                      ),
                      style: const TextStyle(fontSize: 12),
                      onChanged: (val) => provider.updateBeritaAcara(
                        val.trim().isEmpty ? null : val.trim(),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
            ],

            // ===================================================================
            // 5. LIVE SUMMARY BAR & AKSI CEPAT (SEMUA HADIR & KOSONGKAN)
            // ===================================================================
            if (provider.muridList.isNotEmpty) ...[
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF101710)
                      : const Color(0xFFF3F4F1),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isDark
                        ? AppColors.outlineDark
                        : AppColors.outlineLight,
                  ),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Wrap(
                        spacing: 6,
                        runSpacing: 4,
                        children: [
                          _buildBadge(
                            'Total: ${provider.totalMurid}',
                            Colors.grey,
                          ),
                          if (provider.countBelumDiisi > 0)
                            _buildBadge(
                              'Belum: ${provider.countBelumDiisi}',
                              AppColors.amberAccent,
                            ),
                          _buildBadge(
                            'Hadir: ${provider.countHadir}',
                            AppColors.primaryLight,
                          ),
                          if (provider.countIzin > 0)
                            _buildBadge(
                              'Izin: ${provider.countIzin}',
                              AppColors.skyBlueAccent,
                            ),
                          if (provider.countSakit > 0)
                            _buildBadge(
                              'Sakit: ${provider.countSakit}',
                              AppColors.amberAccent,
                            ),
                          if (provider.countAlpha > 0)
                            _buildBadge(
                              'Alpha: ${provider.countAlpha}',
                              AppColors.roseDanger,
                            ),
                          if (provider.countDispensasi > 0)
                            _buildBadge(
                              'Dispen: ${provider.countDispensasi}',
                              AppColors.violetAccent,
                            ),
                        ],
                      ),
                    ),
                    PopupMenuButton<String>(
                      icon: const Icon(Icons.more_vert_rounded, size: 20),
                      tooltip: 'Aksi Cepat',
                      onSelected: (val) {
                        if (val == 'hadir') {
                          provider.setAllMuridStatus('Hadir');
                        } else if (val == 'kosong') {
                          provider.setSemuaKosong();
                        }
                      },
                      itemBuilder: (ctx) => [
                        PopupMenuItem(
                          value: 'hadir',
                          child: Row(
                            children: [
                              Icon(
                                Icons.done_all_rounded,
                                size: 18,
                                color: AppColors.primaryLight,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'Hadirkan Semua',
                                style: TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                        const PopupMenuItem(
                          value: 'kosong',
                          child: Row(
                            children: [
                              Icon(
                                Icons.refresh_rounded,
                                size: 18,
                                color: AppColors.amberAccent,
                              ),
                              SizedBox(width: 8),
                              Text(
                                'Kosongkan Semua',
                                style: TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],

            // ===================================================================
            // 6. DAFTAR SANTRI PRESENSI UJIAN
            // ===================================================================
            ...provider.muridList.map((m) {
              final isBelumDiisi = m.status == null || m.status!.isEmpty;
              final statusColor = _getStatusColor(m.status);

              return GlassCard(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Santri (Avatar, Nama, NISM, Badge Status & Lock status)
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: isBelumDiisi
                              ? (isDark
                                    ? Colors.white10
                                    : Colors.black.withValues(alpha: 0.06))
                              : statusColor.withValues(alpha: 0.15),
                          child: Text(
                            m.nama.isNotEmpty ? m.nama[0].toUpperCase() : 'S',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: isBelumDiisi
                                  ? (isDark
                                        ? const Color(0xFF8D9387)
                                        : const Color(0xFF73796E))
                                  : statusColor,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Expanded(
                                    child: Text(
                                      m.nama,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  _buildStatusBadge(m.status, isDark),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'NISM: ${m.nism} • ${m.jenisKelamin == 'L' ? 'Murid Putra' : 'Murid Putri'}',
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
                        if (m.isLocked)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.amberAccent.withValues(
                                alpha: 0.15,
                              ),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.lock_outline_rounded,
                                  size: 10,
                                  color: AppColors.amberAccent,
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  m.lockReason ?? 'Dispensasi',
                                  style: const TextStyle(
                                    fontSize: 9,
                                    color: AppColors.amberAccent,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        IconButton(
                          icon: Icon(
                            m.catatan != null && m.catatan!.isNotEmpty
                                ? Icons.comment_rounded
                                : Icons.mode_comment_outlined,
                            size: 16,
                            color: m.catatan != null && m.catatan!.isNotEmpty
                                ? AppColors.skyBlueAccent
                                : (isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E)),
                          ),
                          tooltip: 'Catatan Presensi',
                          onPressed: () =>
                              _showCatatanDialog(m.muridId, m.nama, m.catatan),
                          visualDensity: VisualDensity.compact,
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    // Garis Pembatas Halus
                    Divider(
                      height: 1,
                      thickness: 0.8,
                      color: isDark
                          ? AppColors.outlineDark.withValues(alpha: 0.4)
                          : AppColors.outlineLight.withValues(alpha: 0.7),
                    ),

                    const SizedBox(height: 10),

                    // Baris 5 Tombol Presensi (H, I, S, A, D) Fleksibel & Mudah Ditekan
                    Row(
                      children: [
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'H',
                            label: 'Hadir',
                            isSelected: m.status == 'Hadir',
                            onTap: () {
                              HapticHelper.light();
                              provider.updateMuridStatus(m.muridId, 'Hadir');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'I',
                            label: 'Izin',
                            isSelected: m.status == 'Izin',
                            onTap: () {
                              HapticHelper.light();
                              provider.updateMuridStatus(m.muridId, 'Izin');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'S',
                            label: 'Sakit',
                            isSelected: m.status == 'Sakit',
                            onTap: () {
                              HapticHelper.light();
                              provider.updateMuridStatus(m.muridId, 'Sakit');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'A',
                            label: 'Alpha',
                            isSelected: m.status == 'Alpha',
                            onTap: () {
                              HapticHelper.light();
                              provider.updateMuridStatus(m.muridId, 'Alpha');
                            },
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          child: StatusPresensiChip(
                            status: 'D',
                            label: 'Dispen',
                            isSelected: m.status == 'Dispensasi',
                            onTap: () {
                              HapticHelper.light();
                              provider.updateMuridStatus(
                                m.muridId,
                                'Dispensasi',
                              );
                            },
                          ),
                        ),
                      ],
                    ),

                    if (m.catatan != null && m.catatan!.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        'Memo: ${m.catatan!}',
                        style: const TextStyle(
                          fontSize: 10,
                          fontStyle: FontStyle.italic,
                          color: AppColors.skyBlueAccent,
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }),

            const SizedBox(height: 16),

            // ===================================================================
            // 7. TOMBOL SIMPAN PRESENSI UJIAN
            // ===================================================================
            if (provider.muridList.isNotEmpty)
              ElevatedButton.icon(
                onPressed: provider.isSaving
                    ? null
                    : () async {
                        HapticHelper.medium();
                        final success = await provider.simpanPresensi();
                        if (context.mounted) {
                          if (success) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: const Text(
                                  'Presensi Ujian murid dan pengawas berhasil disimpan!',
                                ),
                                backgroundColor: AppColors.primaryLight,
                              ),
                            );
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  provider.errorMessage ??
                                      'Gagal menyimpan presensi ujian.',
                                ),
                                backgroundColor: AppColors.roseDanger,
                              ),
                            );
                          }
                        }
                      },
                icon: provider.isSaving
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(Icons.save_rounded, size: 18),
                label: Text(
                  provider.isSaving
                      ? 'Menyimpan Presensi...'
                      : 'Simpan Presensi (${provider.countSudahDiisi}/${provider.totalMurid})',
                ),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'Hadir':
        return AppColors.primaryLight;
      case 'Izin':
        return AppColors.skyBlueAccent;
      case 'Sakit':
        return AppColors.amberAccent;
      case 'Alpha':
        return AppColors.roseDanger;
      case 'Dispensasi':
        return AppColors.violetAccent;
      default:
        return Colors.grey;
    }
  }

  Widget _buildBelumAdaJadwalEmptyState(
    BuildContext context,
    bool isDark,
    PresensiUjianProvider provider,
  ) {
    final ujianName = provider.currentUjian?.namaUjian ?? 'Ujian';
    final tipeUjian = provider.currentUjian?.tipeUjian ?? 'IMDA';
    final ruanganName = provider.currentRuangan?.namaRuangan ?? 'Ruangan ini';
    final levelName = provider.currentRuangan?.namaLevel ?? '';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.amberAccent.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.event_busy_rounded,
                size: 48,
                color: AppColors.amberAccent,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Jadwal $tipeUjian Belum Dibuat',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Jadwal ujian $ujianName ($tipeUjian) untuk $ruanganName ($levelName) belum dibuat oleh Administrator.',
              style: TextStyle(
                fontSize: 13,
                height: 1.4,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xFF101710)
                    : const Color(0xFFF3F4F1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isDark
                      ? AppColors.outlineDark
                      : AppColors.outlineLight,
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 16,
                    color: AppColors.skyBlueAccent,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Silakan hubungi staf/admin MDT untuk membuat jadwal mata pelajaran ujian di web admin.',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark
                            ? const Color(0xFF8D9387)
                            : const Color(0xFF555555),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBelumAdaUjianEmptyState(
    BuildContext context,
    bool isDark,
    PresensiUjianProvider provider,
  ) {
    final ruanganName = provider.currentRuangan?.namaRuangan ?? 'Ruangan ini';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 8),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    .withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.pending_actions_rounded,
                size: 48,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Agenda Ujian Belum Dibuat',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Agenda ujian untuk $ruanganName belum dibuat oleh Administrator.',
              style: TextStyle(
                fontSize: 13,
                height: 1.4,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String? status, bool isDark) {
    final isFilled = status != null && status.isNotEmpty;
    if (!isFilled) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: AppColors.amberAccent.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: AppColors.amberAccent.withValues(alpha: 0.4),
            width: 0.8,
          ),
        ),
        child: const Text(
          'Belum',
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            color: AppColors.amberAccent,
          ),
        ),
      );
    }

    Color bg;
    Color text;
    Color border;

    switch (status) {
      case 'Hadir':
        bg = isDark ? AppColors.hadirBgDark : AppColors.hadirBgLight;
        text = isDark ? AppColors.hadirTextDark : AppColors.hadirTextLight;
        border = isDark ? AppColors.hadirTextDark : const Color(0xFF86EFAC);
        break;
      case 'Sakit':
        bg = isDark ? AppColors.sakitBgDark : AppColors.sakitBgLight;
        text = isDark ? AppColors.sakitTextDark : AppColors.sakitTextLight;
        border = isDark ? AppColors.sakitTextDark : const Color(0xFFFDE68A);
        break;
      case 'Izin':
        bg = isDark ? AppColors.izinBgDark : AppColors.izinBgLight;
        text = isDark ? AppColors.izinTextDark : AppColors.izinTextLight;
        border = isDark ? AppColors.izinTextDark : const Color(0xFF93C5FD);
        break;
      case 'Alpha':
        bg = isDark ? AppColors.alphaBgDark : AppColors.alphaBgLight;
        text = isDark ? AppColors.alphaTextDark : AppColors.alphaTextLight;
        border = isDark ? AppColors.alphaTextDark : const Color(0xFFFCA5A5);
        break;
      case 'Badal':
      case 'Dispensasi':
      default:
        bg = isDark ? AppColors.dispensasiBgDark : AppColors.dispensasiBgLight;
        text = isDark
            ? AppColors.dispensasiTextDark
            : AppColors.dispensasiTextLight;
        border = isDark
            ? AppColors.dispensasiTextDark
            : const Color(0xFFD8B4FE);
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: border.withValues(alpha: 0.5), width: 0.8),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: text,
        ),
      ),
    );
  }

  Widget _buildBadge(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }
}
