import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../providers/auth_provider.dart';
import '../widgets/glass_card.dart';
import 'verify_otp_screen.dart';

class ForgotPasswordScreen extends StatefulWidget {
  final String? initialLoginId;
  const ForgotPasswordScreen({super.key, this.initialLoginId});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _loginIdController;

  @override
  void initState() {
    super.initState();
    _loginIdController = TextEditingController(
      text: widget.initialLoginId ?? '',
    );
  }

  @override
  void dispose() {
    _loginIdController.dispose();
    super.dispose();
  }

  Future<void> _handleRequestOtp() async {
    if (!_formKey.currentState!.validate()) return;
    HapticHelper.medium();

    final authProvider = context.read<AuthProvider>();
    final loginId = _loginIdController.text.trim();
    final result = await authProvider.forgotPassword(loginId);

    if (!mounted) return;
    if (result != null) {
      HapticHelper.confirmSuccess();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result['message'] ?? 'Kode OTP telah dikirim ke email Anda.',
          ),
          backgroundColor: AppColors.primaryLight,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );

      // Langkah 2: Pindah ke Halaman Verifikasi OTP
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => VerifyOtpScreen(
            loginId: loginId,
            maskedEmail: result['email_masked'],
          ),
        ),
      );
    } else {
      HapticHelper.warning();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            authProvider.errorMessage ?? 'Gagal memproses permintaan.',
          ),
          backgroundColor: AppColors.roseDanger,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final authProvider = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Lupa Kata Sandi',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Step Indicator Pill
                Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1B261D)
                          : AppColors.primaryContainerLight,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Langkah 1 dari 3: Masukkan Akun',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Icon Header
                Center(
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1B261D)
                          : AppColors.primaryContainerLight,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      Icons.lock_reset_rounded,
                      size: 38,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                ),
                const SizedBox(height: 20),

                Text(
                  'Pemulihan Akun Asatidz',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Masukkan Username atau Email terdaftar untuk menerima kode verifikasi OTP 6-digit.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                const SizedBox(height: 28),

                GlassCard(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Identitas Akun',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 14),

                      TextFormField(
                        controller: _loginIdController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Username atau Email',
                          hintText: 'nama_ustadz@madrasah.com',
                          prefixIcon: Icon(Icons.account_circle_outlined),
                        ),
                        validator: (val) {
                          if (val == null || val.trim().isEmpty) {
                            return 'Username atau Email wajib diisi';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 24),

                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: authProvider.isLoading
                              ? null
                              : _handleRequestOtp,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                            foregroundColor: isDark
                                ? AppColors.onPrimaryDark
                                : Colors.white,
                            disabledBackgroundColor:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.4),
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(18),
                            ),
                          ),
                          child: authProvider.isLoading
                              ? SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2.5,
                                    color: isDark
                                        ? AppColors.onPrimaryDark
                                        : Colors.white,
                                  ),
                                )
                              : const Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      'Kirim Kode Verifikasi OTP',
                                      style: TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 0.3,
                                      ),
                                    ),
                                    SizedBox(width: 8),
                                    Icon(Icons.send_rounded, size: 18),
                                  ],
                                ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Info Keamanan
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF101710)
                        : const Color(0xFFE8F5E9),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark
                          ? AppColors.outlineDark
                          : AppColors.outlineLight,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.shield_outlined,
                        size: 20,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Kode OTP berlaku selama 15 menit. Jangan berikan kode OTP kepada siapa pun.',
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
        ),
      ),
    );
  }
}
