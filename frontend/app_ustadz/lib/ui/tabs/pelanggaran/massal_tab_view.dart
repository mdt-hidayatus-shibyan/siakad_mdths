import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/pelanggaran_model.dart';
import '../../../providers/pelanggaran_provider.dart';
import '../../widgets/glass_card.dart';

class MassalTabView extends StatefulWidget {
  const MassalTabView({super.key});

  @override
  State<MassalTabView> createState() => _MassalTabViewState();
}

class _MassalTabViewState extends State<MassalTabView> {
  final _nomorPoinController = TextEditingController();
  final _keteranganController = TextEditingController();
  final _searchMuridController = TextEditingController();
  DateTime _tanggal = DateTime.now();

  int? _selectedRuanganId;
  final Set<int> _selectedMuridIds = {};
  ReferensiPelanggaranItem? _selectedReferensi;
  bool _isSaving = false;
  String _muridFilterQuery = '';

  @override
  void initState() {
    super.initState();
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
      }
    });

    _nomorPoinController.addListener(_onNomorPoinChanged);
    _searchMuridController.addListener(() {
      setState(() {
        _muridFilterQuery = _searchMuridController.text.trim().toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    _nomorPoinController.removeListener(_onNomorPoinChanged);
    _nomorPoinController.dispose();
    _keteranganController.dispose();
    _searchMuridController.dispose();
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

  Future<void> _handleSubmitMassal() async {
    if (_selectedRuanganId == null ||
        _selectedMuridIds.isEmpty ||
        _selectedReferensi == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'Harap lengkapi ruangan, pilih minimal 1 santri, dan nomor poin!',
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
    final count = _selectedMuridIds.length;
    final success = await provider.simpanPelanggaranMassal(
      tanggal: DateHelper.toYmd(_tanggal),
      ruanganId: _selectedRuanganId!,
      muridIds: _selectedMuridIds.toList(),
      referensiId: _selectedReferensi!.id,
      keterangan: _keteranganController.text.trim(),
    );

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Berhasil mencatat pelanggaran #${_selectedReferensi!.id} untuk $count santri!',
          ),
          backgroundColor: AppColors.amberAccent,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
      setState(() {
        _selectedMuridIds.clear();
        _keteranganController.clear();
        _nomorPoinController.clear();
        _selectedReferensi = null;
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            provider.errorMessage ?? 'Gagal mencatat pelanggaran massal.',
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

    final filteredMurid = muridList.where((m) {
      if (_muridFilterQuery.isEmpty) return true;
      return m.namaLengkap.toLowerCase().contains(_muridFilterQuery) ||
          m.nism.toLowerCase().contains(_muridFilterQuery);
    }).toList();

    Color badgeColor = AppColors.amberAccent;
    if (_selectedReferensi?.kategori == 'Sedang') {
      badgeColor = Colors.orange;
    }
    if (_selectedReferensi?.kategori == 'Berat') {
      badgeColor = const Color(0xFFF43F5E);
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 120),
      children: [
        // CARD INFORMASI CARA KERJA
        GlassCard(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color:
                      (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                          .withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  Icons.group_add_rounded,
                  color: isDark
                      ? AppColors.primaryDark
                      : AppColors.primaryLight,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Pencatatan Pelanggaran Massal',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Catat jenis pelanggaran yang sama untuk beberapa santri sekaligus dalam 1 kelas.',
                      style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // LANGKAH 1: PILIH RUANGAN & TANGGAL
        Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
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
                        horizontal: 12,
                        vertical: 10,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(
                        Icons.meeting_room_rounded,
                        size: 18,
                      ),
                    ),
                    items: ruanganList.map((r) {
                      return DropdownMenuItem<int>(
                        value: r.id,
                        child: Text(
                          r.namaRuangan,
                          style: const TextStyle(fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null && val != _selectedRuanganId) {
                        setState(() {
                          _selectedRuanganId = val;
                          _selectedMuridIds.clear();
                        });
                        provider.fetchMuridByRuangan(val);
                      }
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Tanggal:',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                InkWell(
                  onTap: _handlePilihTanggal,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    height: 48,
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1E293B)
                          : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isDark
                            ? const Color(0xFF334155)
                            : const Color(0xFFCBD5E1),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.calendar_today_rounded, size: 14),
                        const SizedBox(width: 6),
                        Text(
                          DateHelper.formatTanggalIndo(_tanggal),
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 16),

        // LANGKAH 2: FILTER MURID BERDASARKAN RUANGAN
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              '2. Centang Murid (${_selectedMuridIds.length}/${muridList.length} Terpilih):',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            Row(
              children: [
                TextButton(
                  onPressed: muridList.isEmpty
                      ? null
                      : () {
                          setState(() {
                            if (_selectedMuridIds.length == muridList.length) {
                              _selectedMuridIds.clear();
                            } else {
                              _selectedMuridIds.addAll(
                                muridList.map((m) => m.id),
                              );
                            }
                          });
                        },
                  style: TextButton.styleFrom(
                    visualDensity: VisualDensity.compact,
                  ),
                  child: Text(
                    _selectedMuridIds.length == muridList.length
                        ? 'Batal Semua'
                        : 'Pilih Semua',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 6),

        // Search Murid Box
        TextField(
          controller: _searchMuridController,
          decoration: InputDecoration(
            hintText: 'Cari nama murid di kelas ini...',
            prefixIcon: const Icon(Icons.search_rounded, size: 18),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 12,
              vertical: 8,
            ),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
        const SizedBox(height: 8),

        // Checklist Santri Container
        Container(
          constraints: const BoxConstraints(maxHeight: 220),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF162016) : Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isDark ? const Color(0xFF263326) : const Color(0xFFE2E8F0),
            ),
          ),
          child: provider.isLoadingMurid
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: CircularProgressIndicator(),
                  ),
                )
              : filteredMurid.isEmpty
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: Text(
                      'Tidak ada data santri.',
                      style: TextStyle(fontSize: 12),
                    ),
                  ),
                )
              : ListView.separated(
                  shrinkWrap: true,
                  itemCount: filteredMurid.length,
                  separatorBuilder: (_, __) =>
                      const Divider(height: 1, thickness: 0.5),
                  itemBuilder: (ctx, idx) {
                    final m = filteredMurid[idx];
                    final isChecked = _selectedMuridIds.contains(m.id);

                    return CheckboxListTile(
                      dense: true,
                      value: isChecked,
                      activeColor: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                      checkColor: isDark
                          ? AppColors.onPrimaryDark
                          : Colors.white,
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 0,
                      ),
                      title: Text(
                        m.namaLengkap,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      subtitle: Text(
                        'NISM: ${m.nism}',
                        style: TextStyle(
                          fontSize: 10,
                          color: isDark
                              ? const Color(0xFF94A3B8)
                              : const Color(0xFF64748B),
                        ),
                      ),
                      secondary: m.totalPoin > 0
                          ? Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
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
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFFF43F5E),
                                ),
                              ),
                            )
                          : null,
                      onChanged: (val) {
                        setState(() {
                          if (val == true) {
                            _selectedMuridIds.add(m.id);
                          } else {
                            _selectedMuridIds.remove(m.id);
                          }
                        });
                      },
                    );
                  },
                ),
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
              label: const Text('Buka Katalog', style: TextStyle(fontSize: 11)),
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
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
          ),
        ),
        const SizedBox(height: 8),

        // PREVIEW NAMA PELANGGARAN OTOMATIS
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

        // LANGKAH 4: KETERANGAN
        const Text(
          '4. Keterangan Tambahan (Opsional):',
          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: _keteranganController,
          maxLines: 2,
          decoration: InputDecoration(
            hintText: 'Misal: Terlambat bersama-sama pada jam pertama...',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
          ),
        ),
        const SizedBox(height: 20),

        // TOMBOL SUBMIT MASSAL
        SizedBox(
          width: double.infinity,
          height: 48,
          child: ElevatedButton(
            onPressed:
                (_isSaving ||
                    _selectedMuridIds.isEmpty ||
                    _selectedReferensi == null)
                ? null
                : _handleSubmitMassal,
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark
                  ? AppColors.primaryDark
                  : AppColors.primaryLight,
              foregroundColor: isDark ? AppColors.onPrimaryDark : Colors.white,
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
                      color: isDark ? AppColors.onPrimaryDark : Colors.white,
                    ),
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.group_add_rounded, size: 18),
                      const SizedBox(width: 8),
                      Text(
                        'Simpan Pelanggaran Massal (${_selectedMuridIds.length} Murid)',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
          ),
        ),
      ],
    );
  }
}
