import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/auth_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../main/main_screen.dart';
import '../widgets/glass_card.dart';

class GantiPinAwalScreen extends StatefulWidget {
  final String oldPin;

  const GantiPinAwalScreen({super.key, this.oldPin = '112233'});

  @override
  State<GantiPinAwalScreen> createState() => _GantiPinAwalScreenState();
}

class _GantiPinAwalScreenState extends State<GantiPinAwalScreen> {
  final _formKey = GlobalKey<FormState>();
  final _pinBaruController = TextEditingController();
  final _konfirmasiController = TextEditingController();

  bool _obscurePinBaru = true;
  bool _obscureKonfirmasi = true;
  String? _errorMessage;

  @override
  void dispose() {
    _pinBaruController.dispose();
    _konfirmasiController.dispose();
    super.dispose();
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) {
      HapticHelper.error();
      return;
    }

    final pinBaru = _pinBaruController.text.trim();
    final konfirmasi = _konfirmasiController.text.trim();

    if (pinBaru == widget.oldPin) {
      HapticHelper.error();
      setState(() {
        _errorMessage =
            'PIN baru tidak boleh sama dengan PIN default (112233). Silakan buat PIN lain.';
      });
      return;
    }

    if (pinBaru != konfirmasi) {
      HapticHelper.error();
      setState(() {
        _errorMessage = 'Konfirmasi PIN baru tidak sesuai.';
      });
      return;
    }

    setState(() {
      _errorMessage = null;
    });

    HapticHelper.medium();
    final auth = context.read<AuthProvider>();
    final res = await auth.updatePin(widget.oldPin, pinBaru);

    if (!mounted) return;

    if (res['success'] == true) {
      HapticHelper.heavy();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'PIN Keamanan baru berhasil disimpan! Selamat datang.',
          ),
          backgroundColor: AppColors.primaryLight,
          behavior: SnackBarBehavior.floating,
        ),
      );

      // Refresh dashboard dan lanjut ke MainScreen
      context.read<DashboardProvider>().fetchDashboard();
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainScreen()),
        (route) => false,
      );
    } else {
      HapticHelper.error();
      setState(() {
        _errorMessage = res['message'] ?? 'Gagal memperbarui PIN keamanan.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final auth = context.watch<AuthProvider>();
    final wali = auth.currentWali;

    return PopScope(
      canPop: false, // Wajib ubah PIN saat pertama kali login
      child: Scaffold(
        backgroundColor: isDark
            ? AppColors.surfaceDark
            : AppColors.surfaceLight,
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              physics: const BouncingScrollPhysics(),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Icon Header
                  Container(
                    width: 78,
                    height: 78,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: isDark
                          ? AppColors.primaryDark.withValues(alpha: 0.15)
                          : AppColors.primaryLight.withValues(alpha: 0.1),
                    ),
                    child: Center(
                      child: Icon(
                        Icons.security_rounded,
                        size: 40,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  Text(
                    'Wajib Ubah PIN Keamanan',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.4,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Assalamu\'alaikum, ${wali?.namaKepalaKeluarga ?? "Wali Murid"}.\nDemi keamanan akun dan data murid, silakan ubah PIN default (112233) menjadi 6 digit PIN baru pilihan Anda.',
                    style: TextStyle(
                      fontSize: 13,
                      height: 1.45,
                      fontWeight: FontWeight.w500,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),

                  GlassCard(
                    padding: const EdgeInsets.all(22),
                    borderRadius: 24,
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (_errorMessage != null) ...[
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.roseDanger.withValues(
                                  alpha: 0.12,
                                ),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(
                                  color: AppColors.roseDanger.withValues(
                                    alpha: 0.3,
                                  ),
                                ),
                              ),
                              child: Row(
                                children: [
                                  const Icon(
                                    Icons.error_outline_rounded,
                                    size: 18,
                                    color: AppColors.roseDanger,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      _errorMessage!,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.roseDanger,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),
                          ],

                          // PIN Baru
                          Text(
                            'Buat PIN Baru (6 Digit)',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: isDark ? Colors.white70 : Colors.black87,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _pinBaruController,
                            keyboardType: TextInputType.number,
                            obscureText: _obscurePinBaru,
                            maxLength: 6,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontSize: 20,
                              letterSpacing: 8,
                              fontWeight: FontWeight.w800,
                            ),
                            decoration: InputDecoration(
                              counterText: '',
                              hintText: '••••••',
                              hintStyle: TextStyle(
                                fontSize: 20,
                                letterSpacing: 8,
                                color: isDark ? Colors.white24 : Colors.black26,
                              ),
                              filled: true,
                              fillColor: isDark
                                  ? AppColors.surfaceContainerLowDark
                                  : AppColors.surfaceLight,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.outlineDark
                                      : AppColors.outlineLight,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.outlineDark
                                      : AppColors.outlineLight,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                  width: 2,
                                ),
                              ),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _obscurePinBaru
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  size: 20,
                                  color: isDark
                                      ? Colors.white38
                                      : Colors.black38,
                                ),
                                onPressed: () {
                                  setState(
                                    () => _obscurePinBaru = !_obscurePinBaru,
                                  );
                                },
                              ),
                            ),
                            validator: (val) {
                              if (val == null || val.trim().isEmpty) {
                                return 'PIN baru wajib diisi.';
                              }
                              if (val.trim().length != 6 ||
                                  !RegExp(r'^[0-9]+$').hasMatch(val.trim())) {
                                return 'PIN harus berupa 6 digit angka.';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),

                          // Konfirmasi PIN Baru
                          Text(
                            'Ulangi PIN Baru',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: isDark ? Colors.white70 : Colors.black87,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _konfirmasiController,
                            keyboardType: TextInputType.number,
                            obscureText: _obscureKonfirmasi,
                            maxLength: 6,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontSize: 20,
                              letterSpacing: 8,
                              fontWeight: FontWeight.w800,
                            ),
                            decoration: InputDecoration(
                              counterText: '',
                              hintText: '••••••',
                              hintStyle: TextStyle(
                                fontSize: 20,
                                letterSpacing: 8,
                                color: isDark ? Colors.white24 : Colors.black26,
                              ),
                              filled: true,
                              fillColor: isDark
                                  ? AppColors.surfaceContainerLowDark
                                  : AppColors.surfaceLight,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.outlineDark
                                      : AppColors.outlineLight,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.outlineDark
                                      : AppColors.outlineLight,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                  width: 2,
                                ),
                              ),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _obscureKonfirmasi
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  size: 20,
                                  color: isDark
                                      ? Colors.white38
                                      : Colors.black38,
                                ),
                                onPressed: () {
                                  setState(
                                    () => _obscureKonfirmasi =
                                        !_obscureKonfirmasi,
                                  );
                                },
                              ),
                            ),
                            validator: (val) {
                              if (val == null || val.trim().isEmpty) {
                                return 'Konfirmasi PIN wajib diisi.';
                              }
                              if (val.trim() !=
                                  _pinBaruController.text.trim()) {
                                return 'Konfirmasi PIN tidak cocok.';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 24),

                          // Tombol Simpan
                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton(
                              onPressed: auth.isLoading ? null : _submit,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                                foregroundColor: isDark
                                    ? Colors.black
                                    : Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                elevation: 0,
                              ),
                              child: auth.isLoading
                                  ? SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2.5,
                                        color: isDark
                                            ? Colors.black
                                            : Colors.white,
                                      ),
                                    )
                                  : const Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.check_circle_rounded,
                                          size: 20,
                                        ),
                                        SizedBox(width: 8),
                                        Text(
                                          'Simpan PIN & Lanjutkan',
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w900,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
