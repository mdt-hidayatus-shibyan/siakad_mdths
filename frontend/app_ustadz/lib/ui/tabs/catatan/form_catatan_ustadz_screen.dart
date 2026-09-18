import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/catatan_ustadz_model.dart';
import '../../../providers/catatan_ustadz_provider.dart';
import '../../widgets/custom_app_bar.dart';

class FormCatatanUstadzScreen extends StatefulWidget {
  final CatatanUstadzItem? existingItem;

  const FormCatatanUstadzScreen({super.key, this.existingItem});

  @override
  State<FormCatatanUstadzScreen> createState() =>
      _FormCatatanUstadzScreenState();
}

class _FormCatatanUstadzScreenState extends State<FormCatatanUstadzScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _judulCtrl;
  late TextEditingController _isiCtrl;

  String _selectedTarget = 'murid';
  String _selectedKategori = 'Keluhan Murid';
  String _selectedUrgensi = 'Sedang';

  MuridOptionItem? _selectedMurid;
  RuanganOptionItem? _selectedRuanganFasilitas;
  File? _pickedImage;
  final ImagePicker _picker = ImagePicker();

  final List<String> _kategoriList = [
    'Keluhan Murid',
    'KBM & Perkembangan Akademik',
    'Fasilitas Madrasah',
    'Evaluasi & Saran',
    'Lainnya',
  ];

  final List<String> _urgensiList = [
    'Rendah',
    'Sedang',
    'Tinggi',
    'Penting / Mendesak',
  ];

  bool get isEdit => widget.existingItem != null;

  @override
  void initState() {
    super.initState();
    final item = widget.existingItem;
    _judulCtrl = TextEditingController(text: item?.judul ?? '');
    _isiCtrl = TextEditingController(text: item?.isiCatatan ?? '');

    if (item != null) {
      _selectedTarget = item.targetTipe;
      _selectedKategori = item.kategori;
      _selectedUrgensi = item.tingkatUrgensi;
    }

    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final provider = context.read<CatatanUstadzProvider>();
      await provider.fetchOptions();

      if (item != null && provider.optionsData != null) {
        if (item.muridId != null) {
          final found = provider.optionsData!.murids
              .where((m) => m.id == item.muridId)
              .toList();
          if (found.isNotEmpty) {
            setState(() {
              _selectedMurid = found.first;
            });
          }
        }
        if (item.ruanganId != null) {
          final foundR = provider.optionsData!.ruangans
              .where((r) => r.id == item.ruanganId)
              .toList();
          if (foundR.isNotEmpty) {
            setState(() {
              _selectedRuanganFasilitas = foundR.first;
            });
          }
        }
      }
    });
  }

  @override
  void dispose() {
    _judulCtrl.dispose();
    _isiCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    HapticHelper.light();
    try {
      final XFile? file = await _picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 85,
      );
      if (file != null) {
        setState(() {
          _pickedImage = File(file.path);
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Gagal mengambil gambar: $e')));
      }
    }
  }

  void _showImageSourceDialog() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? const Color(0xFF1B231B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Pilih Sumber Foto Lampiran',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : Colors.black87,
                ),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const Icon(Icons.camera_alt_rounded),
                title: const Text('Kamera Langsung'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickImage(ImageSource.camera);
                },
              ),
              ListTile(
                leading: const Icon(Icons.photo_library_rounded),
                title: const Text('Galeri HP'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickImage(ImageSource.gallery);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showMuridPickerModal(
    List<MuridOptionItem> listMurid,
    List<RuanganOptionItem> listRuangan,
  ) {
    HapticHelper.light();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).brightness == Brightness.dark
          ? const Color(0xFF121712)
          : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => _MuridPickerModal(
        listMurid: listMurid,
        listRuangan: listRuangan,
        initialSelected: _selectedMurid,
        onSelected: (m) {
          setState(() {
            _selectedMurid = m;
          });
        },
      ),
    );
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedTarget == 'murid' && _selectedMurid == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih murid yang dilaporkan terlebih dahulu.'),
        ),
      );
      return;
    }

    HapticHelper.medium();
    final provider = context.read<CatatanUstadzProvider>();

    int? resolvedRuanganId;
    if (_selectedTarget == 'murid') {
      resolvedRuanganId = _selectedMurid?.ruanganId;
    } else if (_selectedTarget == 'madrasah') {
      resolvedRuanganId = _selectedRuanganFasilitas?.id;
    }

    bool success = false;
    if (isEdit) {
      success = await provider.updateCatatan(
        widget.existingItem!.id,
        judul: _judulCtrl.text.trim(),
        kategori: _selectedKategori,
        targetTipe: _selectedTarget,
        isiCatatan: _isiCtrl.text.trim(),
        tingkatUrgensi: _selectedUrgensi,
        muridId: _selectedTarget == 'murid' ? _selectedMurid?.id : null,
        ruanganId: resolvedRuanganId,
        fotoFilePath: _pickedImage?.path,
      );
    } else {
      success = await provider.simpanCatatan(
        judul: _judulCtrl.text.trim(),
        kategori: _selectedKategori,
        targetTipe: _selectedTarget,
        isiCatatan: _isiCtrl.text.trim(),
        tingkatUrgensi: _selectedUrgensi,
        muridId: _selectedTarget == 'murid' ? _selectedMurid?.id : null,
        ruanganId: resolvedRuanganId,
        fotoFilePath: _pickedImage?.path,
      );
    }

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            isEdit
                ? 'Catatan berhasil diperbarui!'
                : 'Catatan baru berhasil disimpan!',
          ),
          backgroundColor: const Color(0xFF059669),
        ),
      );
      Navigator.pop(context, true);
    } else if (mounted && provider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(provider.errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<CatatanUstadzProvider>();
    final options = provider.optionsData;

    return Scaffold(
      appBar: CustomAppBar(
        titleText: isEdit ? 'Edit Catatan' : 'Tambah Catatan Baru',
        subtitleText: 'Laporan & keluhan ustadz',
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        physics: const BouncingScrollPhysics(),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Target Sasaran Catatan
              _buildSectionLabel('Sasaran Catatan / Laporan *', isDark),
              const SizedBox(height: 8),
              Row(
                children: [
                  _buildTargetOption(
                    id: 'murid',
                    label: 'Murid',
                    icon: Icons.person_rounded,
                    isDark: isDark,
                  ),
                  const SizedBox(width: 8),
                  _buildTargetOption(
                    id: 'madrasah',
                    label: 'Madrasah',
                    icon: Icons.school_rounded,
                    isDark: isDark,
                  ),
                  const SizedBox(width: 8),
                  _buildTargetOption(
                    id: 'umum',
                    label: 'Umum',
                    icon: Icons.chat_bubble_outline_rounded,
                    isDark: isDark,
                  ),
                ],
              ),
              const SizedBox(height: 18),

              // Pilih Murid (Jika Sasaran: Murid)
              if (_selectedTarget == 'murid') ...[
                _buildSectionLabel('Pilih Murid yang Dicatat *', isDark),
                const SizedBox(height: 8),
                InkWell(
                  onTap: () {
                    if (options != null) {
                      _showMuridPickerModal(options.murids, options.ruangans);
                    }
                  },
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 13,
                    ),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1B231B) : Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : const Color(0xFFCBD5E1),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.person_search_rounded,
                          size: 20,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            _selectedMurid != null
                                ? '${_selectedMurid!.namaLengkap} (${_selectedMurid!.nism}) • ${_selectedMurid!.ruangan}'
                                : (provider.isLoadingOptions
                                      ? 'Memuat daftar murid...'
                                      : 'Ketuk untuk memilih murid...'),
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: _selectedMurid != null
                                  ? FontWeight.bold
                                  : FontWeight.normal,
                              color: _selectedMurid != null
                                  ? (isDark ? Colors.white : Colors.black87)
                                  : (isDark ? Colors.white38 : Colors.black38),
                            ),
                          ),
                        ),
                        const Icon(Icons.arrow_drop_down_rounded),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 18),
              ],

              // Pilih Ruangan / Kelas (Jika Sasaran: Madrasah)
              if (_selectedTarget == 'madrasah') ...[
                _buildSectionLabel(
                  'Pilih Ruangan / Fasilitas Kelas (Opsional)',
                  isDark,
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<RuanganOptionItem?>(
                  initialValue: _selectedRuanganFasilitas,
                  isExpanded: true,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: isDark ? const Color(0xFF1B231B) : Colors.white,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  hint: Text(
                    'Pilih ruangan jika berkaitan (Opsional)...',
                    style: TextStyle(
                      fontSize: 13,
                      color: isDark ? Colors.white38 : Colors.black38,
                    ),
                  ),
                  items: [
                    const DropdownMenuItem<RuanganOptionItem?>(
                      value: null,
                      child: Text(
                        'Semua Ruangan / Fasilitas Umum',
                        style: TextStyle(fontSize: 13),
                      ),
                    ),
                    if (options != null)
                      ...options.ruangans.map(
                        (r) => DropdownMenuItem<RuanganOptionItem?>(
                          value: r,
                          child: Text(
                            r.namaRuangan,
                            style: const TextStyle(fontSize: 13),
                          ),
                        ),
                      ),
                  ],
                  onChanged: (val) {
                    setState(() {
                      _selectedRuanganFasilitas = val;
                    });
                  },
                ),
                const SizedBox(height: 18),
              ],

              // Kategori & Tingkat Urgensi
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Kategori
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionLabel('Kategori *', isDark),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<String>(
                          initialValue: _selectedKategori,
                          isExpanded: true,
                          decoration: InputDecoration(
                            filled: true,
                            fillColor: isDark
                                ? const Color(0xFF1B231B)
                                : Colors.white,
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 10,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                          ),
                          items: _kategoriList
                              .map(
                                (k) => DropdownMenuItem(
                                  value: k,
                                  child: Text(
                                    k,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              )
                              .toList(),
                          onChanged: (val) {
                            if (val != null) {
                              setState(() => _selectedKategori = val);
                            }
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),

                  // Urgensi
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionLabel('Urgensi *', isDark),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<String>(
                          initialValue: _selectedUrgensi,
                          isExpanded: true,
                          decoration: InputDecoration(
                            filled: true,
                            fillColor: isDark
                                ? const Color(0xFF1B231B)
                                : Colors.white,
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 10,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                          ),
                          items: _urgensiList
                              .map(
                                (u) => DropdownMenuItem(
                                  value: u,
                                  child: Text(
                                    u,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              )
                              .toList(),
                          onChanged: (val) {
                            if (val != null) {
                              setState(() => _selectedUrgensi = val);
                            }
                          },
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),

              // Judul Catatan
              _buildSectionLabel('Judul Catatan / Pokok Masalah *', isDark),
              const SizedBox(height: 8),
              TextFormField(
                controller: _judulCtrl,
                decoration: InputDecoration(
                  hintText:
                      'Cth: Keluhan sering terlambat / Fasilitas kelas rusak...',
                  filled: true,
                  fillColor: isDark ? const Color(0xFF1B231B) : Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                ),
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                ),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) {
                    return 'Judul catatan tidak boleh kosong.';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 18),

              // Isi Catatan
              _buildSectionLabel('Uraian / Keterangan Lengkap *', isDark),
              const SizedBox(height: 8),
              TextFormField(
                controller: _isiCtrl,
                maxLines: 5,
                decoration: InputDecoration(
                  hintText:
                      'Tuliskan kronologi, observasi, keluhan, atau saran Anda secara rinci...',
                  filled: true,
                  fillColor: isDark ? const Color(0xFF1B231B) : Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  contentPadding: const EdgeInsets.all(14),
                ),
                style: const TextStyle(fontSize: 13, height: 1.4),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) {
                    return 'Isi catatan tidak boleh kosong.';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 18),

              // Lampiran Foto (Opsional)
              _buildSectionLabel('Lampiran Foto / Bukti (Opsional)', isDark),
              const SizedBox(height: 8),
              if (_pickedImage != null)
                Stack(
                  children: [
                    Container(
                      height: 180,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFCBD5E1),
                        ),
                        image: DecorationImage(
                          image: FileImage(_pickedImage!),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      right: 8,
                      child: InkWell(
                        onTap: () {
                          setState(() {
                            _pickedImage = null;
                          });
                        },
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: const BoxDecoration(
                            color: Colors.black54,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.close_rounded,
                            color: Colors.white,
                            size: 18,
                          ),
                        ),
                      ),
                    ),
                  ],
                )
              else if (widget.existingItem?.lampiranFotoUrl != null)
                Stack(
                  children: [
                    Container(
                      height: 180,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark
                              ? AppColors.outlineDark
                              : const Color(0xFFCBD5E1),
                        ),
                        image: DecorationImage(
                          image: NetworkImage(
                            widget.existingItem!.lampiranFotoUrl!,
                          ),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 8,
                      right: 8,
                      child: ElevatedButton.icon(
                        onPressed: _showImageSourceDialog,
                        icon: const Icon(Icons.edit_rounded, size: 16),
                        label: const Text(
                          'Ganti Foto',
                          style: TextStyle(fontSize: 11),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.black54,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ),
                  ],
                )
              else
                InkWell(
                  onTap: _showImageSourceDialog,
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    height: 100,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1B231B) : Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : const Color(0xFFCBD5E1),
                        style: BorderStyle.solid,
                      ),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.add_a_photo_outlined,
                          size: 28,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Ketuk untuk melampirkan foto / bukti',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              const SizedBox(height: 32),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton.icon(
                  onPressed: provider.isSaving ? null : _handleSave,
                  icon: provider.isSaving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.save_rounded, size: 20),
                  label: Text(
                    provider.isSaving
                        ? 'Menyimpan...'
                        : (isEdit
                              ? 'Simpan Perubahan Catatan'
                              : 'Kirim & Simpan Catatan'),
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    foregroundColor: isDark ? Colors.black : Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionLabel(String label, bool isDark) {
    return Text(
      label,
      style: TextStyle(
        fontSize: 12,
        fontWeight: FontWeight.w800,
        color: isDark ? Colors.white70 : Colors.black87,
        letterSpacing: 0.2,
      ),
    );
  }

  Widget _buildTargetOption({
    required String id,
    required String label,
    required IconData icon,
    required bool isDark,
  }) {
    final isSelected = _selectedTarget == id;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;

    return Expanded(
      child: InkWell(
        onTap: () {
          HapticHelper.light();
          setState(() {
            _selectedTarget = id;
            if (id == 'madrasah' && _selectedKategori == 'Keluhan Murid') {
              _selectedKategori = 'Fasilitas Madrasah';
            }
          });
        },
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected
                ? primaryColor.withValues(alpha: 0.12)
                : (isDark ? const Color(0xFF1B231B) : Colors.white),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected
                  ? primaryColor
                  : (isDark ? AppColors.outlineDark : const Color(0xFFCBD5E1)),
              width: isSelected ? 1.8 : 1.0,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 20,
                color: isSelected
                    ? primaryColor
                    : (isDark ? Colors.white54 : Colors.black54),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                  color: isSelected
                      ? primaryColor
                      : (isDark ? Colors.white70 : Colors.black87),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _MuridPickerModal extends StatefulWidget {
  final List<MuridOptionItem> listMurid;
  final List<RuanganOptionItem> listRuangan;
  final MuridOptionItem? initialSelected;
  final ValueChanged<MuridOptionItem> onSelected;

  const _MuridPickerModal({
    required this.listMurid,
    required this.listRuangan,
    this.initialSelected,
    required this.onSelected,
  });

  @override
  State<_MuridPickerModal> createState() => _MuridPickerModalState();
}

class _MuridPickerModalState extends State<_MuridPickerModal> {
  final TextEditingController _searchCtrl = TextEditingController();
  String? _selectedRuangan;
  List<String> _ruanganList = [];
  List<MuridOptionItem> _filtered = [];

  @override
  void initState() {
    super.initState();
    // Susun daftar ruangan terurut berdasarkan urutan_level dari backend
    final List<String> list = [];
    for (final r in widget.listRuangan) {
      final name = r.namaRuangan.trim();
      if (name.isNotEmpty && !list.contains(name)) {
        list.add(name);
      }
    }
    // Fallback jika listRuangan kosong
    if (list.isEmpty) {
      for (final m in widget.listMurid) {
        final name = m.ruangan.trim();
        if (name.isNotEmpty && name != '-' && !list.contains(name)) {
          list.add(name);
        }
      }
    }
    _ruanganList = list;

    // Pilih ruangan awal
    if (widget.initialSelected != null &&
        _ruanganList.contains(widget.initialSelected!.ruangan.trim())) {
      _selectedRuangan = widget.initialSelected!.ruangan.trim();
    } else if (_ruanganList.isNotEmpty) {
      _selectedRuangan = _ruanganList.first;
    }

    _applyFilter();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _applyFilter() {
    final query = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      _filtered = widget.listMurid.where((m) {
        // Filter Ruangan Kelas (Wajib pilih ruangan tertentu)
        if (_selectedRuangan != null && _selectedRuangan!.isNotEmpty) {
          if (m.ruangan.trim().toLowerCase() !=
              _selectedRuangan!.toLowerCase()) {
            return false;
          }
        }

        // Filter NISM / Nama
        if (query.isEmpty) return true;
        final matchName = m.namaLengkap.toLowerCase().contains(query);
        final matchNism = m.nism.toLowerCase().contains(query);
        return matchName || matchNism;
      }).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Drag handle
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: isDark ? Colors.white24 : Colors.black26,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
          ),
          const SizedBox(height: 14),

          // Header Title & Counter
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Pilih Murid',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Pilih ruangan kelas & cari berdasarkan NISM / nama',
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark ? Colors.white54 : Colors.black54,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: primaryColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${_filtered.length} Murid',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: primaryColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Form Select: Ruangan Kelas (Urut berdasarkan urutan_level)
          if (_ruanganList.isNotEmpty) ...[
            DropdownButtonFormField<String>(
              initialValue: _selectedRuangan,
              isExpanded: true,
              decoration: InputDecoration(
                labelText: 'Pilih Ruangan Kelas *',
                labelStyle: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white70 : Colors.black87,
                ),
                prefixIcon: const Icon(
                  Icons.door_front_door_outlined,
                  size: 20,
                ),
                filled: true,
                fillColor: isDark
                    ? const Color(0xFF1B231B)
                    : const Color(0xFFF1F5F9),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(
                    color: isDark
                        ? AppColors.outlineDark
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(
                    color: isDark
                        ? AppColors.outlineDark
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
              ),
              items: _ruanganList.map((ruang) {
                return DropdownMenuItem(
                  value: ruang,
                  child: Text(
                    'Ruangan / Kelas $ruang',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null) {
                  HapticHelper.light();
                  setState(() {
                    _selectedRuangan = val;
                  });
                  _applyFilter();
                }
              },
            ),
            const SizedBox(height: 12),
          ],

          // Search Field: Cari NISM atau Nama Murid
          TextField(
            controller: _searchCtrl,
            decoration: InputDecoration(
              hintText: _selectedRuangan != null
                  ? 'Ketik NISM atau nama murid di kelas $_selectedRuangan...'
                  : 'Ketik NISM atau nama murid...',
              prefixIcon: const Icon(Icons.search_rounded, size: 20),
              suffixIcon: _searchCtrl.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18),
                      onPressed: () {
                        _searchCtrl.clear();
                        _applyFilter();
                      },
                    )
                  : null,
              filled: true,
              fillColor: isDark
                  ? const Color(0xFF1B231B)
                  : const Color(0xFFF1F5F9),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide.none,
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 11,
              ),
            ),
            onChanged: (_) => _applyFilter(),
          ),
          const SizedBox(height: 12),

          // Murid List View
          Expanded(
            child: _filtered.isEmpty
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.person_search_rounded,
                            size: 44,
                            color: isDark ? Colors.white24 : Colors.black26,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Murid Tidak Ditemukan',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: isDark ? Colors.white70 : Colors.black87,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _searchCtrl.text.isNotEmpty
                                ? 'Tidak ada murid dengan NISM/nama "${_searchCtrl.text}" di ruangan $_selectedRuangan.'
                                : 'Belum ada data murid di ruangan $_selectedRuangan.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? Colors.white38 : Colors.black45,
                            ),
                          ),
                          if (_searchCtrl.text.isNotEmpty) ...[
                            const SizedBox(height: 14),
                            OutlinedButton.icon(
                              onPressed: () {
                                _searchCtrl.clear();
                                _applyFilter();
                              },
                              icon: const Icon(Icons.clear_rounded, size: 16),
                              label: const Text(
                                'Hapus Pencarian',
                                style: TextStyle(fontSize: 12),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  )
                : ListView.separated(
                    itemCount: _filtered.length,
                    padding: const EdgeInsets.only(bottom: 24),
                    separatorBuilder: (_, __) => Divider(
                      height: 1,
                      color: isDark ? Colors.white10 : Colors.black12,
                    ),
                    itemBuilder: (ctx, idx) {
                      final item = _filtered[idx];
                      final isSelected = widget.initialSelected?.id == item.id;

                      return ListTile(
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        leading: CircleAvatar(
                          backgroundColor: primaryColor.withValues(alpha: 0.15),
                          child: Icon(
                            Icons.person_rounded,
                            size: 20,
                            color: primaryColor,
                          ),
                        ),
                        title: Text(
                          item.namaLengkap,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.w600,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        subtitle: Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Row(
                            children: [
                              // NISM Badge
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? Colors.white10
                                      : const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'NISM: ${item.nism}',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: isDark
                                        ? Colors.white70
                                        : Colors.black87,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),

                              // Ruangan Badge
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.door_front_door_outlined,
                                      size: 10,
                                      color: primaryColor,
                                    ),
                                    const SizedBox(width: 3),
                                    Text(
                                      item.ruangan,
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                        color: primaryColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        trailing: isSelected
                            ? Icon(
                                Icons.check_circle_rounded,
                                color: primaryColor,
                              )
                            : null,
                        onTap: () {
                          HapticHelper.light();
                          widget.onSelected(item);
                          Navigator.pop(ctx);
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
