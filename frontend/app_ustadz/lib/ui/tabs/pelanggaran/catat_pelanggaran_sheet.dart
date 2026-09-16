import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/pelanggaran_model.dart';
import '../../../providers/pelanggaran_provider.dart';
import '../../widgets/glass_card.dart';

class CatatPelanggaranSheet extends StatefulWidget {
  final int? initialRuanganId;
  final int? initialMuridId;

  const CatatPelanggaranSheet({
    super.key,
    this.initialRuanganId,
    this.initialMuridId,
  });

  @override
  State<CatatPelanggaranSheet> createState() => _CatatPelanggaranSheetState();
}

class _CatatPelanggaranSheetState extends State<CatatPelanggaranSheet> {
  final _nomorPoinController = TextEditingController();
  final _keteranganController = TextEditingController();
  DateTime _tanggal = DateTime.now();

  int? _selectedRuanganId;
  int? _selectedMuridId;
  ReferensiPelanggaranItem? _selectedReferensi;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _selectedRuanganId = widget.initialRuanganId;
    _selectedMuridId = widget.initialMuridId;

    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final provider = context.read<PelanggaranProvider>();
      if (provider.ruanganList.isEmpty || provider.referensiList.isEmpty) {
        await provider.fetchAll();
      }

      if (_selectedRuanganId == null && provider.ruanganList.isNotEmpty) {
        _selectedRuanganId = provider.ruanganList.first.id;
      }

      if (_selectedRuanganId != null) {
        await provider.fetchMuridByRuangan(_selectedRuanganId!);
        if (mounted) {
          final muridList = provider.muridList;
          if (_selectedMuridId == null && muridList.isNotEmpty) {
            setState(() {
              _selectedMuridId = muridList.first.id;
            });
          }
        }
      }
    });

    _nomorPoinController.addListener(_onNomorPoinChanged);
  }

  @override
  void dispose() {
    _nomorPoinController.removeListener(_onNomorPoinChanged);
    _nomorPoinController.dispose();
    _keteranganController.dispose();
    super.dispose();
  }

  void _onNomorPoinChanged() {
    final text = _nomorPoinController.text.trim();
    if (text.isEmpty) {
      if (_selectedReferensi != null) {
        setState(() => _selectedReferensi = null);
      }
      return;
    }

    final id = int.tryParse(text);
    if (id != null) {
      final provider = context.read<PelanggaranProvider>();
      final found = provider.findReferensiById(id);
      if (found != _selectedReferensi) {
        setState(() {
          _selectedReferensi = found;
        });
      }
    } else {
      if (_selectedReferensi != null) {
        setState(() => _selectedReferensi = null);
      }
    }
  }

  void _pilihDariKatalog() {
    HapticHelper.light();
    final provider = context.read<PelanggaranProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        String searchQuery = '';
        return StatefulBuilder(
          builder: (context, setModalState) {
            final filtered = provider.referensiList.where((r) {
              if (searchQuery.isEmpty) return true;
              return r.namaPelanggaran.toLowerCase().contains(
                    searchQuery.toLowerCase(),
                  ) ||
                  r.id.toString() == searchQuery ||
                  r.kategori.toLowerCase().contains(searchQuery.toLowerCase());
            }).toList();

            return Container(
              height: MediaQuery.of(context).size.height * 0.75,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF131B13) : Colors.white,
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(28),
                ),
              ),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
              child: Column(
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF43483E)
                            : const Color(0xFFC3C8BC),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Pilih Referensi Pelanggaran',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    decoration: InputDecoration(
                      hintText: 'Cari nama pelanggaran / nomor ID...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20),
                      filled: true,
                      fillColor: isDark
                          ? const Color(0xFF1B241B)
                          : const Color(0xFFF1F5F9),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 10,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                    onChanged: (val) {
                      setModalState(() => searchQuery = val);
                    },
                  ),
                  const SizedBox(height: 10),
                  Expanded(
                    child: filtered.isEmpty
                        ? const Center(
                            child: Text('Pelanggaran tidak ditemukan.'),
                          )
                        : ListView.separated(
                            itemCount: filtered.length,
                            separatorBuilder: (_, __) =>
                                const Divider(height: 1, thickness: 0.5),
                            itemBuilder: (ctx, idx) {
                              final item = filtered[idx];
                              final isSelected =
                                  _selectedReferensi?.id == item.id;
                              Color katColor = AppColors.amberAccent;
                              if (item.kategori == 'Sedang') {
                                katColor = Colors.orange;
                              }
                              if (item.kategori == 'Berat') {
                                katColor = const Color(0xFFF43F5E);
                              }

                              return ListTile(
                                leading: Container(
                                  width: 38,
                                  height: 38,
                                  alignment: Alignment.center,
                                  decoration: BoxDecoration(
                                    color: katColor.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                      color: katColor.withValues(alpha: 0.4),
                                    ),
                                  ),
                                  child: Text(
                                    '#${item.id}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      color: katColor,
                                    ),
                                  ),
                                ),
                                title: Text(
                                  item.namaPelanggaran,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                subtitle: Text(
                                  'Kategori: ${item.kategori} • +${item.poinFormatted} Poin Sanksi',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                ),
                                trailing: isSelected
                                    ? Icon(
                                        Icons.check_circle_rounded,
                                        color: AppColors.primaryLight,
                                      )
                                    : const Icon(
                                        Icons.chevron_right_rounded,
                                        size: 18,
                                      ),
                                onTap: () {
                                  _nomorPoinController.text = item.id
                                      .toString();
                                  Navigator.pop(context);
                                },
                              );
                            },
                          ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _handlePilihTanggal() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tanggal,
      firstDate: DateTime(2024),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );
    if (picked != null) {
      setState(() => _tanggal = picked);
    }
  }

  Future<void> _handleSubmit() async {
    if (_selectedRuanganId == null ||
        _selectedMuridId == null ||
        _selectedReferensi == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'Harap lengkapi ruangan, santri, dan nomor poin pelanggaran!',
          ),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);
    HapticHelper.medium();

    final provider = context.read<PelanggaranProvider>();
    final success = await provider.simpanPelanggaran(
      tanggal: DateHelper.toYmd(_tanggal),
      ruanganId: _selectedRuanganId!,
      muridId: _selectedMuridId!,
      referensiId: _selectedReferensi!.id,
      keterangan: _keteranganController.text.trim(),
    );

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (success) {
      final isDark = Theme.of(context).brightness == Brightness.dark;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Pelanggaran #${_selectedReferensi!.id} berhasil dicatat ke Buku Kasus!',
          ),
          backgroundColor: isDark
              ? AppColors.primaryDark
              : AppColors.primaryLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            provider.errorMessage ?? 'Gagal mencatat pelanggaran santri.',
          ),
          backgroundColor: Colors.red.shade800,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PelanggaranProvider>();
    final ruanganList = provider.ruanganList;
    final muridList = provider.muridList;

    Color badgeColor = AppColors.amberAccent;
    if (_selectedReferensi?.kategori == 'Sedang') {
      badgeColor = Colors.orange;
    }
    if (_selectedReferensi?.kategori == 'Berat') {
      badgeColor = const Color(0xFFF43F5E);
    }

    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF101710) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        12,
        20,
        MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Handle bar
            Center(
              child: Container(
                width: 38,
                height: 4,
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF43483E)
                      : const Color(0xFFC3C8BC),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header Title & Tanggal
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Catat Pelanggaran Murid',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                ),
                GestureDetector(
                  onTap: _handlePilihTanggal,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1E293B)
                          : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark
                            ? const Color(0xFF334155)
                            : const Color(0xFFE2E8F0),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.calendar_today_rounded, size: 12),
                        const SizedBox(width: 6),
                        Text(
                          DateHelper.formatTanggalIndo(_tanggal),
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // LANGKAH 1: PILIH RUANGAN
            const Text(
              '1. Pilih Ruangan / Kelas:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              initialValue: _selectedRuanganId,
              isExpanded: true,
              decoration: InputDecoration(
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                prefixIcon: const Icon(Icons.meeting_room_rounded, size: 20),
              ),
              items: ruanganList.map((r) {
                return DropdownMenuItem<int>(
                  value: r.id,
                  child: Text(
                    '${r.namaRuangan} (${r.levelNama})',
                    style: const TextStyle(fontSize: 13),
                  ),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null && val != _selectedRuanganId) {
                  setState(() {
                    _selectedRuanganId = val;
                    _selectedMuridId = null;
                  });
                  provider.fetchMuridByRuangan(val).then((_) {
                    if (mounted && provider.muridList.isNotEmpty) {
                      setState(() {
                        _selectedMuridId = provider.muridList.first.id;
                      });
                    }
                  });
                }
              },
            ),
            const SizedBox(height: 16),

            // LANGKAH 2: FILTER MURID BERDASARKAN RUANGAN
            const Text(
              '2. Pilih Murid yang Melanggar:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            if (provider.isLoadingMurid)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: LinearProgressIndicator(minHeight: 3),
              )
            else if (muridList.isEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF1E293B)
                      : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text(
                  'Tidak ada santri di ruangan ini.',
                  style: TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
                ),
              )
            else
              DropdownButtonFormField<int>(
                initialValue: _selectedMuridId,
                isExpanded: true,
                decoration: InputDecoration(
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  prefixIcon: const Icon(Icons.person_rounded, size: 20),
                ),
                items: muridList.map((m) {
                  return DropdownMenuItem<int>(
                    value: m.id,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            '${m.namaLengkap} (${m.nism})',
                            style: const TextStyle(fontSize: 13),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (m.totalPoin > 0)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 1.5,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(
                                0xFFF43F5E,
                              ).withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              '${m.totalPoin} Poin',
                              style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFFF43F5E),
                              ),
                            ),
                          ),
                      ],
                    ),
                  );
                }).toList(),
                onChanged: (val) {
                  setState(() => _selectedMuridId = val);
                },
              ),
            const SizedBox(height: 16),

            // LANGKAH 3: INPUT NOMOR POIN / ID PELANGGARAN
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '3. Masukkan Nomor ID Pelanggaran:',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
                TextButton.icon(
                  onPressed: _pilihDariKatalog,
                  icon: const Icon(Icons.list_alt_rounded, size: 14),
                  label: const Text(
                    'Buka Katalog',
                    style: TextStyle(fontSize: 11),
                  ),
                  style: TextButton.styleFrom(
                    visualDensity: VisualDensity.compact,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            TextField(
              controller: _nomorPoinController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                hintText: 'Ketik nomor ID (contoh: 1, 5, 12...)',
                prefixIcon: const Icon(Icons.tag_rounded, size: 20),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.search_rounded),
                  tooltip: 'Pilih dari katalog',
                  onPressed: _pilihDariKatalog,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
            const SizedBox(height: 8),

            // KARTU PREVIEW NAMA PELANGGARAN OTOMATIS
            if (_selectedReferensi != null)
              GlassCard(
                padding: const EdgeInsets.all(14),
                margin: const EdgeInsets.only(bottom: 12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: badgeColor.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: badgeColor.withValues(alpha: 0.4),
                        ),
                      ),
                      child: Icon(
                        Icons.warning_amber_rounded,
                        color: badgeColor,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 7,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: badgeColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'ID #${_selectedReferensi!.id} • ${_selectedReferensi!.kategori.toUpperCase()}',
                                  style: TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                    color: badgeColor,
                                  ),
                                ),
                              ),
                              Text(
                                '+${_selectedReferensi!.poinFormatted} Poin',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  color: badgeColor,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            _selectedReferensi!.namaPelanggaran,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              )
            else if (_nomorPoinController.text.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(10),
                margin: const EdgeInsets.only(bottom: 12),
                decoration: BoxDecoration(
                  color: Colors.red.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.red.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.info_outline_rounded,
                      size: 16,
                      color: Colors.red,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Nomor ID #${_nomorPoinController.text} tidak ditemukan. Silakan klik "Buka Katalog".',
                        style: const TextStyle(fontSize: 11, color: Colors.red),
                      ),
                    ),
                  ],
                ),
              ),

            // LANGKAH 4: KETERANGAN / KRONOLOGI
            const Text(
              '4. Catatan / Kronologi Tambahan (Opsional):',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: _keteranganController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'Misal: Terlambat masuk dan membuat gaduh...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
            const SizedBox(height: 20),

            // SUBMIT BUTTON
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed:
                    (_isSaving ||
                        _selectedMuridId == null ||
                        _selectedReferensi == null)
                    ? null
                    : _handleSubmit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                  foregroundColor: isDark
                      ? AppColors.onPrimaryDark
                      : Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: _isSaving
                    ? SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: isDark
                              ? AppColors.onPrimaryDark
                              : Colors.white,
                        ),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.save_rounded, size: 18),
                          SizedBox(width: 8),
                          Text(
                            'Masukkan ke Buku Kasus',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
