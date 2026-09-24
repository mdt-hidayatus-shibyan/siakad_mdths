import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/presensi_model.dart';
import '../../../providers/presensi_provider.dart';

class CheckinUstadzSheet extends StatefulWidget {
  final SesiPresensiUstadzItem sesi;

  const CheckinUstadzSheet({super.key, required this.sesi});

  static Future<void> show(BuildContext context, SesiPresensiUstadzItem sesi) {
    HapticHelper.light();
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => CheckinUstadzSheet(sesi: sesi),
    );
  }

  @override
  State<CheckinUstadzSheet> createState() => _CheckinUstadzSheetState();
}

class _CheckinUstadzSheetState extends State<CheckinUstadzSheet> {
  late String _selectedStatus;
  int? _selectedBadalId;
  int? _selectedUstadzId;
  late final TextEditingController _ketController;

  @override
  void initState() {
    super.initState();
    _selectedStatus = widget.sesi.sudahCheckin ? widget.sesi.status : 'Hadir';
    _selectedBadalId = widget.sesi.ustadzPenggantiId;
    if (widget.sesi.daftarUstadz.isNotEmpty) {
      _selectedUstadzId = widget.sesi.daftarUstadz.first.id;
    }
    _ketController = TextEditingController(text: widget.sesi.keterangan ?? '');
  }

  @override
  void dispose() {
    _ketController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PresensiProvider>();
    final isBadalNeeded =
        _selectedStatus == 'Izin' ||
        _selectedStatus == 'Sakit' ||
        _selectedStatus == 'Kosong';

    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161E16) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Handle Bar
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.withValues(alpha: 0.4),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            Text(
              widget.sesi.isMilikWali
                  ? 'Presensi Ustadz (Kelas Binaan)'
                  : 'Check-In Presensi Mengajar',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white : Colors.black87,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Guru: ${widget.sesi.guruPengajar} • ${widget.sesi.mapel} - ${widget.sesi.ruangan} (${widget.sesi.jam})',
              style: TextStyle(
                fontSize: 12,
                color: isDark
                    ? const Color(0xFF8D9387)
                    : const Color(0xFF73796E),
              ),
            ),
            const SizedBox(height: 18),

            // Pilihan Ustadz jika Team Teaching (Multi-Pengampu)
            if (widget.sesi.isTeamTeaching &&
                widget.sesi.daftarUstadz.length > 1) ...[
              const Text(
                'Pilih Guru Pengampu:',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: widget.sesi.daftarUstadz.map((u) {
                  final isSelected = _selectedUstadzId == u.id;
                  return ChoiceChip(
                    label: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (u.isUtama) ...[
                          const Icon(
                            Icons.star_rounded,
                            size: 14,
                            color: Colors.amber,
                          ),
                          const SizedBox(width: 4),
                        ],
                        Text(u.nama),
                      ],
                    ),
                    selected: isSelected,
                    onSelected: (val) {
                      if (val) {
                        setState(() {
                          _selectedUstadzId = u.id;
                          if (u.sudahCheckin) {
                            _selectedStatus = u.status;
                          }
                        });
                      }
                    },
                    selectedColor: isDark
                        ? AppColors.primaryDark.withValues(alpha: 0.25)
                        : AppColors.primaryLight.withValues(alpha: 0.15),
                    labelStyle: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 11,
                      color: isSelected
                          ? (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                          : (isDark ? Colors.white70 : Colors.black87),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 16),
            ],

            // Status Radio/Chips
            const Text(
              'Pilih Status Kehadiran:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: ['Hadir', 'Izin', 'Sakit', 'Alpha'].map((st) {
                final isSelected = _selectedStatus == st;
                return ChoiceChip(
                  label: Text(st),
                  selected: isSelected,
                  onSelected: (val) {
                    if (val) {
                      setState(() => _selectedStatus = st);
                    }
                  },
                  selectedColor: st == 'Hadir'
                      ? (isDark
                            ? AppColors.hadirBgDark
                            : AppColors.hadirBgLight)
                      : (isDark
                            ? const Color(0xFF451A03)
                            : const Color(0xFFFEF3C7)),
                  labelStyle: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 12,
                    color: isSelected
                        ? (st == 'Hadir'
                              ? (isDark
                                    ? AppColors.hadirTextDark
                                    : AppColors.hadirTextLight)
                              : (isDark
                                    ? AppColors.sakitTextDark
                                    : AppColors.sakitTextLight))
                        : (isDark ? Colors.white70 : Colors.black87),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 16),

            // Ustadz Pengganti (Badal) Dropdown
            if (isBadalNeeded) ...[
              const Text(
                'Guru Badal / Pengganti (Opsional):',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 6),
              DropdownButtonFormField<int?>(
                initialValue: _selectedBadalId,
                isExpanded: true,
                decoration: InputDecoration(
                  hintText: 'Pilih Ustadz Pengganti...',
                  prefixIcon: const Icon(Icons.swap_horiz_rounded),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 10,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                items: [
                  const DropdownMenuItem<int?>(
                    value: null,
                    child: Text('Tanpa Guru Badal (Kosong)'),
                  ),
                  ...provider.daftarBadalList.map((u) {
                    return DropdownMenuItem<int?>(
                      value: u.id,
                      child: Text(u.namaLengkap),
                    );
                  }),
                ],
                onChanged: (val) {
                  setState(() => _selectedBadalId = val);
                },
              ),
              const SizedBox(height: 14),
            ],

            // Keterangan Text Field
            const Text(
              'Catatan / Keterangan (Opsional):',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            TextFormField(
              controller: _ketController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText:
                    'Contoh: Hadir tepat waktu / Izin keperluan keluarga...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 10,
                ),
              ),
            ),
            const SizedBox(height: 22),

            // Save Button
            ElevatedButton.icon(
              onPressed: provider.isCheckingInUstadz
                  ? null
                  : () async {
                      Navigator.pop(context);
                      final success = await provider.checkinUstadz(
                        jadwalId: widget.sesi.jadwalId,
                        ustadzId: _selectedUstadzId,
                        status: _selectedStatus,
                        ustadzPenggantiId: _selectedBadalId,
                        keterangan: _ketController.text.trim(),
                      );

                      if (!context.mounted) return;
                      if (success) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              'Presensi sesi ${widget.sesi.mapel} berhasil disimpan!',
                            ),
                            backgroundColor: AppColors.hadirTextLight,
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
                              provider.errorMessage ??
                                  'Gagal menyimpan check-in.',
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
              icon: const Icon(Icons.check_rounded, size: 18),
              label: const Text('Simpan Presensi Mengajar'),
            ),
          ],
        ),
      ),
    );
  }
}
