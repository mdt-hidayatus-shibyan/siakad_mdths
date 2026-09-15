import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/haptic_helper.dart';
import '../../core/utils/session_helper.dart';
import '../../providers/auth_provider.dart';
import '../navigation/main_navigation_shell.dart';
import '../widgets/glass_card.dart';
import 'forgot_password_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _loginIdController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _loginIdController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    HapticHelper.medium();

    SessionHelper.resetAllProviders(context);
    final authProvider = context.read<AuthProvider>();
    final success = await authProvider.login(
      _loginIdController.text.trim(),
      _passwordController.text.trim(),
    );

    if (!mounted) return;
    if (success) {
      HapticHelper.confirmSuccess();
      Navigator.pushReplacement(
        context,
        PageRouteBuilder(
          pageBuilder: (_, __, ___) => const MainNavigationShell(),
          transitionsBuilder: (_, a, __, c) =>
              FadeTransition(opacity: a, child: c),
        ),
      );
    } else {
      HapticHelper.warning();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Login gagal.'),
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
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 12),
                  // Logo & App Title
                  Center(
                    child: Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF0F2313)
                            : AppColors.primaryContainerLight,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color:
                                (isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight)
                                    .withValues(alpha: 0.2),
                            blurRadius: 20,
                          ),
                        ],
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(10),
                        child: Image.asset(
                          'assets/logo_mdt.png',
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text(
                    'Ustadz - MDTHS',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      letterSpacing: -0.5,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Aplikasi Khusus Ustadz & Wali Ruangan',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: isDark
                          ? const Color(0xFF8D9387)
                          : const Color(0xFF73796E),
                    ),
                  ),
                  const SizedBox(height: 32),

                  // Login Form Card
                  GlassCard(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Login ID Field (Email / Username)
                        TextFormField(
                          controller: _loginIdController,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'Username atau Email',
                            hintText: 'nama_ustadz@madrasah.com',
                            prefixIcon: Icon(Icons.person_outline_rounded),
                          ),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Username / Email wajib diisi';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),

                        // Password Field
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          decoration: InputDecoration(
                            labelText: 'Kata Sandi',
                            prefixIcon: const Icon(Icons.lock_outline_rounded),
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword
                                    ? Icons.visibility_outlined
                                    : Icons.visibility_off_outlined,
                              ),
                              onPressed: () {
                                setState(() {
                                  _obscurePassword = !_obscurePassword;
                                });
                              },
                            ),
                          ),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Kata sandi wajib diisi';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 8),

                        // Lupa Kata Sandi Button
                        Align(
                          alignment: Alignment.centerRight,
                          child: TextButton(
                            onPressed: () {
                              HapticHelper.light();
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ForgotPasswordScreen(
                                    initialLoginId: _loginIdController.text
                                        .trim(),
                                  ),
                                ),
                              );
                            },
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 4,
                                vertical: 2,
                              ),
                              visualDensity: VisualDensity.compact,
                            ),
                            child: const Text(
                              'Lupa Kata Sandi?',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Submit Button
                        SizedBox(
                          width: double.infinity,
                          height: 52,
                          child: ElevatedButton(
                            onPressed: authProvider.isLoading
                                ? null
                                : _handleLogin,
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
                                        'Masuk Sekarang',
                                        style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w900,
                                          letterSpacing: 0.3,
                                        ),
                                      ),
                                      SizedBox(width: 8),
                                      Icon(
                                        Icons.arrow_forward_rounded,
                                        size: 20,
                                      ),
                                    ],
                                  ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Info Role Lock Notice
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF101710)
                          : const Color(0xFFE8F5E9),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.verified_user_rounded,
                          size: 18,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Hak akses server dikunci khusus untuk Ustadz terdaftar.',
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
      ),
    );
  }
}
