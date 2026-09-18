import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../core/utils/session_helper.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/kas_provider.dart';
import '../../../providers/theme_provider.dart';
import '../../auth/login_screen.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/digital_signature_pad.dart';
import '../../widgets/glass_card.dart';
import '../laporan/laporan_pengampu_screen.dart';
import '../laporan/laporan_ruangan_screen.dart';
import 'hubungi_admin_screen.dart';
import 'tentang_aplikasi_screen.dart';

class AkunTab extends StatefulWidget {
  const AkunTab({super.key});

  @override
  State<AkunTab> createState() => _AkunTabState();
}

class _AkunTabState extends State<AkunTab> {
  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().fetchProfile();
      context.read<KasProvider>().fetchPengaturan();
    });
  }

  void _showThemeDialog() {
    HapticHelper.medium();
    final themeProvider = context.read<ThemeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(
              Icons.palette_outlined,
              size: 22,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
            const SizedBox(width: 8),
            const Text(
              'Pilih Tema Tampilan',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildThemeOption(
              ctx,
              title: 'Mode Terang',
              subtitle: 'Tema standar bersih, cerah & nyaman (Default)',
              icon: Icons.light_mode_rounded,
              selected: themeProvider.themeMode == ThemeMode.light,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.light);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
            const SizedBox(height: 8),
            _buildThemeOption(
              ctx,
              title: 'Mode Gelap (Super AMOLED)',
              subtitle: 'Latar hitam murni hemat daya layar',
              icon: Icons.dark_mode_rounded,
              selected: themeProvider.themeMode == ThemeMode.dark,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.dark);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
            const SizedBox(height: 8),
            _buildThemeOption(
              ctx,
              title: 'Ikuti Pengaturan Sistem',
              subtitle: 'Menyesuaikan mode perangkat Anda',
              icon: Icons.brightness_auto_rounded,
              selected: themeProvider.themeMode == ThemeMode.system,
              onTap: () {
                themeProvider.setThemeMode(ThemeMode.system);
                Navigator.of(ctx).pop();
              },
              isDark: isDark,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  void _showColorPresetDialog() {
    HapticHelper.medium();
    final themeProvider = context.read<ThemeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.surfaceContainerDark : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Row(
          children: [
            Icon(
              Icons.color_lens_rounded,
              size: 22,
              color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            ),
            const SizedBox(width: 8),
            const Text(
              'Pilih Warna Aplikasi',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
            ),
          ],
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: AppColors.presets.map((preset) {
                final isSelected = themeProvider.colorPresetKey == preset.key;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: InkWell(
                    onTap: () {
                      HapticHelper.selection();
                      themeProvider.setColorPreset(preset.key);
                      Navigator.of(ctx).pop();
                    },
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? (isDark
                                  ? preset.primaryDark.withValues(alpha: 0.2)
                                  : preset.primaryLight.withValues(alpha: 0.1))
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isSelected
                              ? (isDark
                                    ? preset.primaryDark
                                    : preset.primaryLight)
                              : (isDark ? Colors.white12 : Colors.black12),
                          width: isSelected ? 1.5 : 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              color: isDark
                                  ? preset.primaryDark
                                  : preset.primaryLight,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: Colors.white.withValues(alpha: 0.5),
                                width: 2,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color:
                                      (isDark
                                              ? preset.primaryDark
                                              : preset.primaryLight)
                                          .withValues(alpha: 0.3),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: isSelected
                                ? const Icon(
                                    Icons.check_rounded,
                                    size: 16,
                                    color: Colors.white,
                                  )
                                : null,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  preset.name,
                                  style: TextStyle(
                                    fontSize: 13,
                                    fontWeight: isSelected
                                        ? FontWeight.w900
                                        : FontWeight.w700,
                                    color: isDark
                                        ? Colors.white
                                        : Colors.black87,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  preset.subtitle,
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: isDark
                                        ? Colors.white54
                                        : Colors.black54,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (isSelected)
                            Icon(
                              Icons.check_circle_rounded,
                              size: 18,
                              color: isDark
                                  ? preset.primaryDark
                                  : preset.primaryLight,
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }

  Widget _buildThemeOption(
    BuildContext context, {
    required String title,
    required String subtitle,
    required IconData icon,
    required bool selected,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    return InkWell(
      onTap: () {
        HapticHelper.selection();
        onTap();
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: selected
              ? (isDark
                    ? AppColors.primaryDark.withValues(alpha: 0.2)
                    : AppColors.primaryLight.withValues(alpha: 0.1))
              : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: selected
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                : (isDark ? Colors.white12 : Colors.black12),
            width: selected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: selected
                  ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                  : (isDark ? Colors.white60 : Colors.black54),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: selected ? FontWeight.w900 : FontWeight.w700,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: 10,
                      color: isDark ? Colors.white54 : Colors.black54,
                    ),
                  ),
                ],
              ),
            ),
            if (selected)
              Icon(
                Icons.check_circle_rounded,
                size: 18,
                color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
              ),
          ],
        ),
      ),
    );
  }

  String _getThemeSubtitle(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light:
        return 'Mode Terang (Default)';
      case ThemeMode.dark:
        return 'Mode Gelap (Super AMOLED)';
      case ThemeMode.system:
        return 'Ikuti Pengaturan Sistem';
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    HapticHelper.light();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label berhasil disalin ke papan klip!'),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // =========================================================================
  // 1. MODAL EDIT FOTO PROFIL
  // =========================================================================
  void _showEditFotoModal() {
    HapticHelper.light();
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF101710) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(
                child: Container(
                  width: 36,
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
              const Text(
                'Perbarui Foto Profil',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.primaryLight.withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.camera_alt_rounded,
                    color: AppColors.primaryLight,
                  ),
                ),
                title: const Text(
                  'Ambil dari Kamera',
                  style: TextStyle(fontWeight: FontWeight.w600),
                ),
                subtitle: const Text(
                  'Gunakan kamera ponsel untuk mengambil foto baru',
                  style: TextStyle(fontSize: 11),
                ),
                onTap: () async {
                  Navigator.pop(ctx);
                  final picked = await _picker.pickImage(
                    source: ImageSource.camera,
                    maxWidth: 1024,
                    maxHeight: 1024,
                    imageQuality: 85,
                  );
                  if (picked != null) {
                    _uploadFoto(picked.path);
                  }
                },
              ),
              const Divider(height: 1),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.skyBlueAccent.withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.photo_library_rounded,
                    color: AppColors.skyBlueAccent,
                  ),
                ),
                title: const Text(
                  'Pilih dari Galeri',
                  style: TextStyle(fontWeight: FontWeight.w600),
                ),
                subtitle: const Text(
                  'Unggah foto dari penyimpanan perangkat',
                  style: TextStyle(fontSize: 11),
                ),
                onTap: () async {
                  Navigator.pop(ctx);
                  final picked = await _picker.pickImage(
                    source: ImageSource.gallery,
                    maxWidth: 1024,
                    maxHeight: 1024,
                    imageQuality: 85,
                  );
                  if (picked != null) {
                    _uploadFoto(picked.path);
                  }
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _uploadFoto(String path) async {
    HapticHelper.light();
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Row(
          children: [
            SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: Colors.white,
              ),
            ),
            SizedBox(width: 12),
            Text('Mengunggah foto profil baru...'),
          ],
        ),
        duration: Duration(seconds: 4),
      ),
    );

    final success = await context.read<AuthProvider>().updateFoto(
      filePath: path,
    );
    if (!mounted) return;

    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    if (success) {
      HapticHelper.confirmSuccess();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Foto profil berhasil diperbarui!'),
          backgroundColor: AppColors.primaryLight,
        ),
      );
    } else {
      HapticHelper.warning();
      final err =
          context.read<AuthProvider>().errorMessage ??
          'Gagal mengunggah foto profil.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(err), backgroundColor: AppColors.roseDanger),
      );
    }
  }

  // =========================================================================
  // 2. MODAL EDIT BIODATA USTADZ
  // =========================================================================
  void _showEditBiodataSheet() {
    HapticHelper.light();
    final user = context.read<AuthProvider>().user;
    final formKey = GlobalKey<FormState>();

    final namaController = TextEditingController(text: user?.name ?? '');
    final nikController = TextEditingController(text: user?.nik ?? '');
    final nigmController = TextEditingController(text: user?.nigm ?? '');
    final tempatLahirController = TextEditingController(
      text: user?.tempatLahir ?? '',
    );
    final noHpController = TextEditingController(text: user?.noHp ?? '');
    final alamatController = TextEditingController(text: user?.alamat ?? '');
    final tahunMengajarController = TextEditingController(
      text: user?.tahunMulaiMengajar != null
          ? user!.tahunMulaiMengajar.toString()
          : '',
    );

    String jenisKelamin = user?.jenisKelamin ?? 'L';
    DateTime? selectedDate = user?.tanggalLahir != null
        ? DateTime.tryParse(user!.tanggalLahir!)
        : null;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.88,
            ),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 36,
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
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Edit Biodata Ustadz',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, size: 20),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const Divider(height: 1),
                  const SizedBox(height: 12),

                  Expanded(
                    child: ListView(
                      children: [
                        // NAMA LENGKAP
                        TextFormField(
                          controller: namaController,
                          decoration: const InputDecoration(
                            labelText: 'Nama Lengkap Beserta Gelar *',
                            prefixIcon: Icon(Icons.person_rounded),
                          ),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Nama lengkap wajib diisi';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 12),

                        // NIK & NIGM
                        Row(
                          children: [
                            Expanded(
                              child: TextFormField(
                                controller: nikController,
                                keyboardType: TextInputType.number,
                                maxLength: 16,
                                decoration: const InputDecoration(
                                  labelText: 'NIK (16 Digit)',
                                  counterText: '',
                                  prefixIcon: Icon(Icons.credit_card_rounded),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: TextFormField(
                                controller: nigmController,
                                readOnly: true,
                                decoration: InputDecoration(
                                  labelText: 'NIGM (Read Only)',
                                  hintText: user?.nigm ?? '-',
                                  prefixIcon: const Icon(Icons.badge_rounded),
                                  suffixIcon: const Icon(
                                    Icons.lock_outline_rounded,
                                    size: 16,
                                  ),
                                  filled: true,
                                  fillColor: isDark
                                      ? const Color(0xFF1B241C)
                                      : const Color(0xFFF1F5F9),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // JENIS KELAMIN
                        const Text(
                          'Jenis Kelamin',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Expanded(
                              child: ChoiceChip(
                                label: const Center(
                                  child: Text('Laki-laki (L)'),
                                ),
                                selected: jenisKelamin == 'L',
                                onSelected: (sel) {
                                  if (sel) {
                                    setModalState(() => jenisKelamin = 'L');
                                  }
                                },
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: ChoiceChip(
                                label: const Center(
                                  child: Text('Perempuan (P)'),
                                ),
                                selected: jenisKelamin == 'P',
                                onSelected: (sel) {
                                  if (sel) {
                                    setModalState(() => jenisKelamin = 'P');
                                  }
                                },
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // TEMPAT & TANGGAL LAHIR
                        Row(
                          children: [
                            Expanded(
                              child: TextFormField(
                                controller: tempatLahirController,
                                decoration: const InputDecoration(
                                  labelText: 'Tempat Lahir',
                                  prefixIcon: Icon(Icons.location_city_rounded),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: InkWell(
                                onTap: () async {
                                  final picked = await showDatePicker(
                                    context: context,
                                    initialDate:
                                        selectedDate ?? DateTime(1995, 1, 1),
                                    firstDate: DateTime(1940),
                                    lastDate: DateTime.now(),
                                  );
                                  if (picked != null) {
                                    setModalState(() => selectedDate = picked);
                                  }
                                },
                                child: InputDecorator(
                                  decoration: const InputDecoration(
                                    labelText: 'Tanggal Lahir',
                                    prefixIcon: Icon(
                                      Icons.calendar_month_rounded,
                                    ),
                                  ),
                                  child: Text(
                                    selectedDate != null
                                        ? DateFormat(
                                            'dd/MM/yyyy',
                                          ).format(selectedDate!)
                                        : 'Pilih Tgl',
                                    style: TextStyle(
                                      fontSize: 13,
                                      color: selectedDate == null
                                          ? Colors.grey
                                          : null,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // NO HP & TAHUN MENGAJAR
                        Row(
                          children: [
                            Expanded(
                              child: TextFormField(
                                controller: noHpController,
                                keyboardType: TextInputType.phone,
                                decoration: const InputDecoration(
                                  labelText: 'No. WhatsApp / HP',
                                  prefixIcon: Icon(Icons.phone_rounded),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: TextFormField(
                                controller: tahunMengajarController,
                                keyboardType: TextInputType.number,
                                maxLength: 4,
                                decoration: const InputDecoration(
                                  labelText: 'Tahun Mulai Mengajar',
                                  counterText: '',
                                  prefixIcon: Icon(Icons.history_edu_rounded),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // ALAMAT
                        TextFormField(
                          controller: alamatController,
                          maxLines: 2,
                          decoration: const InputDecoration(
                            labelText: 'Alamat Lengkap',
                            prefixIcon: Icon(Icons.home_rounded),
                          ),
                        ),
                        const SizedBox(height: 18),
                      ],
                    ),
                  ),

                  // SUBMIT BUTTON
                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            if (!formKey.currentState!.validate()) return;
                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final data = {
                              'nama_lengkap': namaController.text.trim(),
                              'nik': nikController.text.trim().isEmpty
                                  ? null
                                  : nikController.text.trim(),
                              'nigm': nigmController.text.trim().isEmpty
                                  ? null
                                  : nigmController.text.trim(),
                              'jenis_kelamin': jenisKelamin,
                              'tempat_lahir':
                                  tempatLahirController.text.trim().isEmpty
                                  ? null
                                  : tempatLahirController.text.trim(),
                              'tanggal_lahir': selectedDate != null
                                  ? DateFormat(
                                      'yyyy-MM-dd',
                                    ).format(selectedDate!)
                                  : null,
                              'no_hp': noHpController.text.trim().isEmpty
                                  ? null
                                  : noHpController.text.trim(),
                              'alamat': alamatController.text.trim().isEmpty
                                  ? null
                                  : alamatController.text.trim(),
                              'tahun_mulai_mengajar':
                                  tahunMengajarController.text.trim().isEmpty
                                  ? null
                                  : tahunMengajarController.text.trim(),
                            };

                            final success = await context
                                .read<AuthProvider>()
                                .updateBiodata(data);

                            if (ctx.mounted) {
                              setModalState(() => isSaving = false);
                              if (success) {
                                Navigator.pop(ctx);
                                HapticHelper.confirmSuccess();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Biodata Ustadz berhasil diperbarui!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                final err =
                                    context.read<AuthProvider>().errorMessage ??
                                    'Gagal memperbarui biodata.';
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(err),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Simpan Perubahan Biodata'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 3. MODAL EDIT AKUN PENGGUNA (USERNAME & EMAIL)
  // =========================================================================
  void _showEditAkunSheet() {
    HapticHelper.light();
    final user = context.read<AuthProvider>().user;
    final formKey = GlobalKey<FormState>();
    final usernameController = TextEditingController(
      text: user?.username ?? '',
    );
    final emailController = TextEditingController(text: user?.email ?? '');
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 36,
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
                  const Text(
                    'Pengaturan Akun Pengguna',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Ubah username dan email yang digunakan untuk masuk aplikasi.',
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // USERNAME
                  TextFormField(
                    controller: usernameController,
                    decoration: const InputDecoration(
                      labelText: 'Username Login *',
                      prefixIcon: Icon(Icons.alternate_email_rounded),
                    ),
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Username wajib diisi';
                      }
                      if (val.trim().length < 3) {
                        return 'Username minimal 3 karakter';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),

                  // EMAIL
                  TextFormField(
                    controller: emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(
                      labelText: 'Alamat Email *',
                      prefixIcon: Icon(Icons.email_outlined),
                    ),
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Email wajib diisi';
                      }
                      if (!val.contains('@') || !val.contains('.')) {
                        return 'Format email tidak valid';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            if (!formKey.currentState!.validate()) return;
                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final success = await context
                                .read<AuthProvider>()
                                .updateAccount(
                                  username: usernameController.text.trim(),
                                  email: emailController.text.trim(),
                                );

                            if (ctx.mounted) {
                              setModalState(() => isSaving = false);
                              if (success) {
                                Navigator.pop(ctx);
                                HapticHelper.confirmSuccess();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Data akun login berhasil diperbarui!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                final err =
                                    context.read<AuthProvider>().errorMessage ??
                                    'Gagal memperbarui akun.';
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(err),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Simpan Akun Pengguna'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 4. MODAL EDIT TANDA TANGAN DIGITAL
  // =========================================================================
  void _showEditTandaTanganSheet() {
    HapticHelper.light();
    final user = context.read<AuthProvider>().user;
    final sigKey = GlobalKey<DigitalSignaturePadState>();
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.85,
            ),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 36,
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Tanda Tangan Digital Ustadz',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 1),
                const SizedBox(height: 10),

                Expanded(
                  child: ListView(
                    children: [
                      // TANDA TANGAN SAAT INI (JIKA ADA)
                      if (user?.tandaTangan != null) ...[
                        const Text(
                          'Tanda Tangan Saat Ini:',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          height: 80,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF162016)
                                : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: isDark
                                  ? const Color(0xFF263326)
                                  : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Center(
                            child: Image.network(
                              user!.tandaTangan!,
                              height: 65,
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => const Text(
                                'Gagal memuat pratinjau tanda tangan',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.grey,
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],

                      const Text(
                        'Goreskan Tanda Tangan Baru:',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),

                      // CANVAS SIGNATURE PAD
                      DigitalSignaturePad(key: sigKey, height: 180),
                      const SizedBox(height: 10),

                      // OPSI ALTERNATIF: UPLOAD BERKAS GAMBAR
                      OutlinedButton.icon(
                        onPressed: () async {
                          final auth = context.read<AuthProvider>();
                          final messenger = ScaffoldMessenger.of(context);
                          final picked = await _picker.pickImage(
                            source: ImageSource.gallery,
                            maxWidth: 800,
                            maxHeight: 400,
                          );
                          if (picked != null) {
                            if (ctx.mounted) {
                              Navigator.pop(ctx);
                            }
                            messenger.showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Mengunggah gambar tanda tangan...',
                                ),
                              ),
                            );
                            final success = await auth.updateTandaTangan(
                              filePath: picked.path,
                            );
                            if (mounted) {
                              if (success) {
                                HapticHelper.confirmSuccess();
                                messenger.showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Tanda tangan digital berhasil diperbarui!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                messenger.showSnackBar(
                                  const SnackBar(
                                    content: Text(
                                      'Gagal memperbarui tanda tangan.',
                                    ),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          }
                        },
                        icon: const Icon(Icons.upload_file_rounded, size: 16),
                        label: const Text(
                          'Atau Unggah Gambar Tanda Tangan dari Galeri',
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 10),
                ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final padState = sigKey.currentState;
                          if (padState == null || padState.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Silakan goreskan tanda tangan pada layar terlebih dahulu.',
                                ),
                              ),
                            );
                            return;
                          }

                          setModalState(() => isSaving = true);
                          HapticHelper.medium();

                          final auth = context.read<AuthProvider>();
                          final base64 = await padState.exportBase64();
                          if (base64 == null) {
                            setModalState(() => isSaving = false);
                            return;
                          }

                          final success = await auth.updateTandaTangan(
                            base64Image: base64,
                          );

                          if (ctx.mounted) {
                            setModalState(() => isSaving = false);
                            if (success) {
                              Navigator.pop(ctx);
                              HapticHelper.confirmSuccess();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Tanda tangan digital berhasil disimpan!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              }
                            } else {
                              final err =
                                  auth.errorMessage ??
                                  'Gagal menyimpan tanda tangan.';
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(err),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          }
                        },
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Simpan Goresan Tanda Tangan'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 5. MODAL PENGATURAN KAS RUANGAN (KHUSUS WALI RUANGAN)
  // =========================================================================
  void _showPengaturanKasSheet() async {
    HapticHelper.light();
    final kasProvider = context.read<KasProvider>();
    await kasProvider.fetchPengaturan();
    if (!mounted) return;

    final pengaturan = kasProvider.pengaturan;
    final nominalLakiController = TextEditingController(
      text: (pengaturan?.nominalLaki ?? 0).toString(),
    );
    final nominalPerempuanController = TextEditingController(
      text: (pengaturan?.nominalPerempuan ?? 0).toString(),
    );
    final formKey = GlobalKey<FormState>();
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 36,
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
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Pengaturan Kas Ruangan Binaan',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Ruangan: ${pengaturan?.namaRuangan ?? "Ruangan Binaan"}',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, size: 20),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const Divider(height: 1),
                  const SizedBox(height: 14),

                  // TARGET KAS PUTRA
                  TextFormField(
                    controller: nominalLakiController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: 'Nominal Kas Murid Putra (Rp) *',
                      prefixIcon: Icon(
                        Icons.payments_rounded,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      hintText: 'Contoh: 0',
                    ),
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Nominal putra wajib diisi';
                      }
                      if (int.tryParse(val.trim()) == null) {
                        return 'Harus berupa angka';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),

                  // TARGET KAS PUTRI
                  TextFormField(
                    controller: nominalPerempuanController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: 'Nominal Kas Murid Putri (Rp) *',
                      prefixIcon: Icon(
                        Icons.payments_rounded,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      hintText: 'Contoh: 0',
                    ),
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) {
                        return 'Nominal putri wajib diisi';
                      }
                      if (int.tryParse(val.trim()) == null) {
                        return 'Harus berupa angka';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            if (!formKey.currentState!.validate()) return;
                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final success = await context
                                .read<KasProvider>()
                                .updatePengaturan(
                                  ruanganId: pengaturan?.ruanganId,
                                  nominalLaki: int.parse(
                                    nominalLakiController.text.trim(),
                                  ),
                                  nominalPerempuan: int.parse(
                                    nominalPerempuanController.text.trim(),
                                  ),
                                );

                            if (ctx.mounted) {
                              setModalState(() => isSaving = false);
                              if (success) {
                                Navigator.pop(ctx);
                                HapticHelper.confirmSuccess();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Pengaturan nominal kas ruangan berhasil disimpan!',
                                    ),
                                    backgroundColor: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                final err =
                                    context.read<KasProvider>().errorMessage ??
                                    'Gagal menyimpan pengaturan kas.';
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(err),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Simpan Pengaturan Kas'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 6. MODAL PERBARUI PASSWORD
  // =========================================================================
  void _showUpdatePasswordDialog() {
    HapticHelper.light();
    final currentPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();
    final formKey = GlobalKey<FormState>();
    bool isSaving = false;
    bool obscureCurrent = true;
    bool obscureNew = true;
    bool obscureConfirm = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF101710) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
            ),
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 36,
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
                  const Text(
                    'Perbarui Kata Sandi Akun',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Masukkan kata sandi saat ini dan tentukan kata sandi baru.',
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Kata Sandi Saat Ini
                  TextFormField(
                    controller: currentPasswordController,
                    obscureText: obscureCurrent,
                    decoration: InputDecoration(
                      labelText: 'Kata Sandi Saat Ini',
                      prefixIcon: const Icon(Icons.lock_outline_rounded),
                      suffixIcon: IconButton(
                        icon: Icon(
                          obscureCurrent
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                        ),
                        onPressed: () => setModalState(
                          () => obscureCurrent = !obscureCurrent,
                        ),
                      ),
                    ),
                    validator: (val) {
                      if (val == null || val.isEmpty) {
                        return 'Kata sandi saat ini wajib diisi';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),

                  // Kata Sandi Baru
                  TextFormField(
                    controller: newPasswordController,
                    obscureText: obscureNew,
                    decoration: InputDecoration(
                      labelText: 'Kata Sandi Baru (Min. 6 Karakter)',
                      prefixIcon: const Icon(Icons.lock_reset_rounded),
                      suffixIcon: IconButton(
                        icon: Icon(
                          obscureNew
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                        ),
                        onPressed: () =>
                            setModalState(() => obscureNew = !obscureNew),
                      ),
                    ),
                    validator: (val) {
                      if (val == null || val.isEmpty) {
                        return 'Kata sandi baru wajib diisi';
                      }
                      if (val.length < 6) {
                        return 'Kata sandi minimal 6 karakter';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),

                  // Konfirmasi Kata Sandi Baru
                  TextFormField(
                    controller: confirmPasswordController,
                    obscureText: obscureConfirm,
                    decoration: InputDecoration(
                      labelText: 'Konfirmasi Kata Sandi Baru',
                      prefixIcon: const Icon(
                        Icons.check_circle_outline_rounded,
                      ),
                      suffixIcon: IconButton(
                        icon: Icon(
                          obscureConfirm
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                        ),
                        onPressed: () => setModalState(
                          () => obscureConfirm = !obscureConfirm,
                        ),
                      ),
                    ),
                    validator: (val) {
                      if (val != newPasswordController.text) {
                        return 'Konfirmasi kata sandi tidak cocok';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            if (!formKey.currentState!.validate()) return;
                            setModalState(() => isSaving = true);
                            HapticHelper.medium();

                            final success = await context
                                .read<AuthProvider>()
                                .updatePassword(
                                  currentPassword:
                                      currentPasswordController.text,
                                  newPassword: newPasswordController.text,
                                  newPasswordConfirmation:
                                      confirmPasswordController.text,
                                );

                            if (ctx.mounted) {
                              setModalState(() => isSaving = false);
                              if (success) {
                                Navigator.pop(ctx);
                                HapticHelper.confirmSuccess();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: const Text(
                                      'Kata sandi akun berhasil diperbarui!',
                                    ),
                                    backgroundColor: AppColors.primaryLight,
                                  ),
                                );
                              } else {
                                final error =
                                    context.read<AuthProvider>().errorMessage ??
                                    'Gagal memperbarui kata sandi.';
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(error),
                                    backgroundColor: AppColors.roseDanger,
                                  ),
                                );
                              }
                            }
                          },
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Simpan Kata Sandi Baru'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // =========================================================================
  // 8. LOGOUT
  // =========================================================================
  void _handleLogout() {
    HapticHelper.warning();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Konfirmasi Keluar'),
        content: const Text(
          'Apakah Anda yakin ingin keluar dari akun Asatidz?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.roseDanger,
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              SessionHelper.resetAllProviders(context);
              await context.read<AuthProvider>().logout();
              if (!mounted) return;
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
            child: const Text('Keluar Akun'),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // HELPER DETAIL ROW WIDGET
  // =========================================================================
  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
    required bool isDark,
    Color? iconColor,
    VoidCallback? onCopy,
  }) {
    final defaultIconColor =
        iconColor ?? (isDark ? AppColors.primaryDark : AppColors.primaryLight);

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: defaultIconColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 16, color: defaultIconColor),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          if (onCopy != null)
            IconButton(
              icon: const Icon(Icons.copy_rounded, size: 15),
              tooltip: 'Salin $label',
              color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
              onPressed: onCopy,
              constraints: const BoxConstraints(),
              padding: const EdgeInsets.all(6),
            ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = context.watch<AuthProvider>().user;
    final themeProvider = context.watch<ThemeProvider>();
    final kasProvider = context.watch<KasProvider>();
    final pengaturanKas = kasProvider.pengaturan;

    return Scaffold(
      appBar: const CustomAppBar(titleText: 'Akun & Pengaturan'),
      body: RefreshIndicator(
        onRefresh: () async {
          final auth = context.read<AuthProvider>();
          final kas = context.read<KasProvider>();
          await auth.fetchProfile();
          if (auth.user?.isWaliRuangan == true) {
            await kas.fetchPengaturan();
          }
        },
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
          children: [
            // =================================================================
            // 1. HERO PROFIL CARD
            // =================================================================
            GlassCard(
              padding: const EdgeInsets.all(20),
              child: Row(
                children: [
                  // AVATAR WITH EDIT BADGE
                  Stack(
                    children: [
                      AppAvatar(
                        radius: 38,
                        imageUrl: user?.photo,
                        name: user?.name ?? '-',
                        cacheDimension: 200,
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: InkWell(
                          onTap: _showEditFotoModal,
                          borderRadius: BorderRadius.circular(16),
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: isDark
                                    ? const Color(0xFF101710)
                                    : Colors.white,
                                width: 2,
                              ),
                            ),
                            child: const Icon(
                              Icons.camera_alt_rounded,
                              size: 13,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user?.name ?? '-',
                          style: const TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Kode: ${user?.kodeUstadz ?? "-"} • NIGM: ${user?.nigm ?? "-"}',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 9,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: user?.isWaliRuangan == true
                                ? (isDark
                                      ? AppColors.primaryContainerDark
                                      : AppColors.primaryContainerLight)
                                : (isDark
                                      ? const Color(0xFF1E293B)
                                      : const Color(0xFFF1F5F9)),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            user?.isWaliRuangan == true
                                ? 'Wali Ruangan: ${user?.ruanganWali ?? "-"}'
                                : 'Ustadz',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: user?.isWaliRuangan == true
                                  ? (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                  : (isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 2. KARTU DATA BIODATA USTADZ LENGKAP
            // =================================================================
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Biodata Pribadi',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                TextButton.icon(
                  onPressed: _showEditBiodataSheet,
                  icon: const Icon(Icons.edit_rounded, size: 14),
                  label: const Text('Edit Biodata'),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    visualDensity: VisualDensity.compact,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Column(
                children: [
                  _buildDetailRow(
                    icon: Icons.credit_card_rounded,
                    label: 'Nomor Induk Kependudukan (NIK)',
                    value: user?.nik ?? 'Belum diisi',
                    isDark: isDark,
                    onCopy: user?.nik != null
                        ? () => _copyToClipboard(user!.nik!, 'NIK')
                        : null,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.badge_rounded,
                    label: 'Nomor Induk Guru Madrasah (NIGM)',
                    value: user?.nigm ?? 'Belum diisi',
                    isDark: isDark,
                    onCopy: user?.nigm != null
                        ? () => _copyToClipboard(user!.nigm!, 'NIGM')
                        : null,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.wc_rounded,
                    label: 'Jenis Kelamin',
                    value: user?.jenisKelamin == 'P'
                        ? 'Perempuan (P)'
                        : 'Laki-laki (L)',
                    isDark: isDark,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.cake_rounded,
                    label: 'Tempat & Tanggal Lahir',
                    value:
                        '${user?.tempatLahir ?? "-"}, ${user?.tanggalLahir != null ? DateFormat('dd MMMM yyyy').format(DateTime.parse(user!.tanggalLahir!)) : "-"}',
                    isDark: isDark,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.phone_android_rounded,
                    label: 'Nomor WhatsApp / HP',
                    value: user?.noHp ?? 'Belum diisi',
                    isDark: isDark,
                    onCopy: user?.noHp != null
                        ? () => _copyToClipboard(user!.noHp!, 'Nomor HP')
                        : null,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.history_edu_rounded,
                    label: 'Masa Pengabdian / Mengajar',
                    value: user?.tahunMulaiMengajar != null
                        ? 'Mulai Mengajar Tahun ${user!.tahunMulaiMengajar}'
                        : 'Belum diatur',
                    isDark: isDark,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.location_on_rounded,
                    label: 'Alamat Tempat Tinggal',
                    value: user?.alamat ?? 'Belum diisi',
                    isDark: isDark,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 3. KARTU TANDA TANGAN DIGITAL
            // =================================================================
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Tanda Tangan Digital',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                TextButton.icon(
                  onPressed: _showEditTandaTanganSheet,
                  icon: const Icon(Icons.draw_rounded, size: 14),
                  label: Text(
                    user?.tandaTangan != null
                        ? 'Ubah Tanda Tangan'
                        : 'Buat Tanda Tangan',
                  ),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    visualDensity: VisualDensity.compact,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (user?.tandaTangan != null) ...[
                    Container(
                      height: 88,
                      width: double.infinity,
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF162016)
                            : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark
                              ? const Color(0xFF263326)
                              : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Center(
                        child: Image.network(
                          user!.tandaTangan!,
                          height: 72,
                          fit: BoxFit.contain,
                          errorBuilder: (_, __, ___) => const Text(
                            'Gagal memuat berkas tanda tangan',
                            style: TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          Icons.verified_rounded,
                          size: 15,
                          color: AppColors.primaryLight,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'Tanda Tangan Digital Terverifikasi',
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
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.symmetric(
                        vertical: 24,
                        horizontal: 16,
                      ),
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF162016)
                            : const Color(0xFFF8FAF7),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark
                              ? const Color(0xFF263326)
                              : const Color(0xFFE2E8F0),
                          style: BorderStyle.solid,
                        ),
                      ),
                      child: Column(
                        children: [
                          Icon(
                            Icons.draw_rounded,
                            size: 32,
                            color: isDark
                                ? const Color(0xFF43483E)
                                : const Color(0xFF94A3B8),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Belum ada tanda tangan digital tersimpan.',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Tanda tangan digunakan untuk pengesahan rapor santri.',
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
                  ],
                ],
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 4. KARTU AKUN PENGGUNA & KEAMANAN
            // =================================================================
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Akun Pengguna & Keamanan',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                TextButton.icon(
                  onPressed: _showEditAkunSheet,
                  icon: const Icon(Icons.manage_accounts_rounded, size: 14),
                  label: const Text('Edit Akun'),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    visualDensity: VisualDensity.compact,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Column(
                children: [
                  _buildDetailRow(
                    icon: Icons.alternate_email_rounded,
                    label: 'Username Login',
                    value: user?.username != null ? '@${user!.username}' : '-',
                    isDark: isDark,
                    onCopy: user?.username != null
                        ? () => _copyToClipboard(user!.username!, 'Username')
                        : null,
                  ),
                  const Divider(height: 1),
                  _buildDetailRow(
                    icon: Icons.email_outlined,
                    label: 'Alamat Email Terdaftar',
                    value: user?.email ?? '-',
                    isDark: isDark,
                    onCopy: user?.email != null
                        ? () => _copyToClipboard(user!.email!, 'Email')
                        : null,
                  ),
                  const Divider(height: 1),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        Icons.lock_reset_rounded,
                        size: 16,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                    title: const Text(
                      'Kata Sandi Akun',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    subtitle: const Text(
                      '•••••••• (Disarankan perbarui berkala)',
                      style: TextStyle(fontSize: 11),
                    ),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: _showUpdatePasswordDialog,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 5. KARTU PENGATURAN KAS RUANGAN (KHUSUS WALI RUANGAN)
            // =================================================================
            if (user?.isWaliRuangan == true) ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Pusat Laporan Ruangan',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
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
                    child: Text(
                      'Wali Ruangan',
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
              const SizedBox(height: 4),
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 4,
                ),
                child: Column(
                  children: [
                    if (user?.isWaliRuangan == true) ...[
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: Container(
                          padding: const EdgeInsets.all(7),
                          decoration: BoxDecoration(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            Icons.admin_panel_settings_rounded,
                            size: 18,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        title: Text(
                          'Laporan ${user?.ruanganWali ?? "Ruangan"}',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        subtitle: const Text(
                          'Presensi kelas, ustadz pengajar, leger, & pelanggaran',
                          style: TextStyle(fontSize: 11),
                        ),
                        trailing: const Icon(Icons.chevron_right_rounded),
                        onTap: () {
                          HapticHelper.light();
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => const LaporanRuanganScreen(),
                            ),
                          );
                        },
                      ),
                      const Divider(height: 1),
                    ],
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Container(
                        padding: const EdgeInsets.all(7),
                        decoration: BoxDecoration(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          Icons.assessment_rounded,
                          size: 18,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                      title: const Text(
                        'Laporan Pengampu',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      subtitle: const Text(
                        'Presensi murid diampu, kehadiran mengajar, & nilai ujian',
                        style: TextStyle(fontSize: 11),
                      ),
                      trailing: const Icon(Icons.chevron_right_rounded),
                      onTap: () {
                        HapticHelper.light();
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const LaporanPengampuScreen(),
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Pengaturan Kas Ruangan Binaan',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  ),
                  TextButton.icon(
                    onPressed: _showPengaturanKasSheet,
                    icon: const Icon(Icons.tune_rounded, size: 14),
                    label: const Text('Ubah Nominal'),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      visualDensity: VisualDensity.compact,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              GlassCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
                child: Column(
                  children: [
                    _buildDetailRow(
                      icon: Icons.meeting_room_rounded,
                      label: 'Ruangan Binaan',
                      value: user?.ruanganWali ?? 'Kelas Binaan',
                      isDark: isDark,
                    ),
                    const Divider(height: 1),
                    _buildDetailRow(
                      icon: Icons.boy_rounded,
                      label: 'Target Kas Murid Putra (Per Murid)',
                      value: DateHelper.formatRupiah(
                        pengaturanKas?.nominalLaki ?? 0,
                      ),
                      isDark: isDark,
                    ),
                    const Divider(height: 1),
                    _buildDetailRow(
                      icon: Icons.girl_rounded,
                      label: 'Target Kas Murid Putri (Per Murid)',
                      value: DateHelper.formatRupiah(
                        pengaturanKas?.nominalPerempuan ?? 0,
                      ),
                      isDark: isDark,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
            ],

            // =================================================================
            // 6. KARTU BANTUAN & LAYANAN ADMIN
            // =================================================================
            const Text(
              'Bantuan & Layanan Admin',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color:
                        (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                            .withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.support_agent_rounded,
                    size: 18,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
                title: const Text(
                  'Hubungi Admin & Pusat Bantuan',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                subtitle: const Text(
                  'Laporan kendala, masalah teknis, & rekomendasi fitur',
                  style: TextStyle(fontSize: 11),
                ),
                trailing: const Icon(Icons.chevron_right_rounded),
                onTap: () {
                  HapticHelper.light();
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const HubungiAdminScreen(),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 7. KARTU TAMPILAN & SISTEM
            // =================================================================
            const Text(
              'Tampilan & Sistem',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Column(
                children: [
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        Icons.brightness_6_rounded,
                        size: 16,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                    title: const Text(
                      'Tema Tampilan',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    subtitle: Text(
                      _getThemeSubtitle(themeProvider.themeMode),
                      style: const TextStyle(fontSize: 11),
                    ),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: _showThemeDialog,
                  ),
                  const Divider(height: 1),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Container(
                      width: 30,
                      height: 30,
                      decoration: BoxDecoration(
                        color: isDark
                            ? themeProvider.activePreset.primaryDark
                            : themeProvider.activePreset.primaryLight,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: isDark ? Colors.white24 : Colors.black12,
                          width: 1.5,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color:
                                (isDark
                                        ? themeProvider.activePreset.primaryDark
                                        : themeProvider
                                              .activePreset
                                              .primaryLight)
                                    .withValues(alpha: 0.25),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: const Icon(
                        Icons.palette_rounded,
                        size: 16,
                        color: Colors.white,
                      ),
                    ),
                    title: const Text(
                      'Warna Aksen Aplikasi',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    subtitle: Text(
                      '${themeProvider.activePreset.name} • ${themeProvider.activePreset.subtitle}',
                      style: const TextStyle(fontSize: 11),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 14,
                          height: 14,
                          decoration: BoxDecoration(
                            color: isDark
                                ? themeProvider.activePreset.primaryDark
                                : themeProvider.activePreset.primaryLight,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Icon(Icons.chevron_right_rounded),
                      ],
                    ),
                    onTap: _showColorPresetDialog,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // =================================================================
            // 8. KARTU INFORMASI APLIKASI
            // =================================================================
            const Text(
              'Informasi Aplikasi',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color:
                        (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight)
                            .withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.info_outline_rounded,
                    size: 18,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
                title: const Text(
                  'Tentang Aplikasi',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                subtitle: const Text(
                  'Versi 1.0.0 • SIAKAD MDT Hidayatus Shibyan',
                  style: TextStyle(fontSize: 11),
                ),
                trailing: const Icon(Icons.chevron_right_rounded),
                onTap: () {
                  HapticHelper.light();
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const TentangAplikasiScreen(),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 28),

            // =================================================================
            // 9. TOMBOL KELUAR
            // =================================================================
            ElevatedButton.icon(
              onPressed: _handleLogout,
              icon: const Icon(Icons.logout_rounded, size: 18),
              label: const Text('Keluar Akun'),
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark
                    ? const Color(0xFF3B1212)
                    : const Color(0xFFFEE2E2),
                foregroundColor: AppColors.roseDanger,
                elevation: 0,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
