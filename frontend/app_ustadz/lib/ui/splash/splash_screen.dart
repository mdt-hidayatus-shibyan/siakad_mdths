import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/theme/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import '../navigation/main_navigation_shell.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );

    _fadeAnimation = CurvedAnimation(parent: _controller, curve: Curves.easeIn);
    _scaleAnimation = Tween<double>(
      begin: 0.85,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutBack));

    _controller.forward();
    _checkSession();
  }

  Future<void> _checkSession() async {
    await Future.delayed(const Duration(milliseconds: 2000));
    if (!mounted) return;

    final authProvider = context.read<AuthProvider>();
    await authProvider.checkAuth();

    if (!mounted) return;
    if (authProvider.isAuthenticated) {
      Navigator.pushReplacement(
        context,
        PageRouteBuilder(
          pageBuilder: (_, __, ___) => const MainNavigationShell(),
          transitionsBuilder: (_, a, __, c) =>
              FadeTransition(opacity: a, child: c),
        ),
      );
    } else {
      Navigator.pushReplacement(
        context,
        PageRouteBuilder(
          pageBuilder: (_, __, ___) => const LoginScreen(),
          transitionsBuilder: (_, a, __, c) =>
              FadeTransition(opacity: a, child: c),
        ),
      );
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
        ),
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: ScaleTransition(
            scale: _scaleAnimation,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Madrasah Crest Icon with Layered Glow
                Container(
                  width: 100,
                  height: 100,
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
                                .withValues(alpha: 0.25),
                        blurRadius: 30,
                        spreadRadius: 8,
                      ),
                    ],
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Image.asset(
                      'assets/logo_mdt.png',
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) => Icon(
                        Icons.school_rounded,
                        size: 52,
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Madrasah Name
                Text(
                  'Ustadz - MDTHS',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Aplikasi Khusus Ustadz & Wali Ruangan',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
                const SizedBox(height: 48),

                // Loading Indicator
                SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    valueColor: AlwaysStoppedAnimation<Color>(
                      isDark ? AppColors.primaryDark : AppColors.primaryLight,
                    ),
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
