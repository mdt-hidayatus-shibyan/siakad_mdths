import 'package:dynamic_color/dynamic_color.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'core/storage/storage_service.dart';
import 'core/theme/app_colors.dart';
import 'core/theme/app_theme.dart';
import 'providers/akademik_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/bantuan_provider.dart';
import 'providers/dashboard_provider.dart';
import 'providers/keuangan_provider.dart';
import 'providers/presensi_provider.dart';
import 'providers/theme_provider.dart';
import 'ui/auth/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi awal SharedPreferences & In-Memory Cache
  await StorageService.init();

  // Enforce Android Full Edge-to-Edge System Bar Transparency (Pixel Style)
  SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
      systemNavigationBarColor: Colors.transparent,
      systemNavigationBarIconBrightness: Brightness.dark,
      systemNavigationBarContrastEnforced: false,
    ),
  );

  runApp(const WaliApp());
}

class WaliApp extends StatelessWidget {
  const WaliApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
        ChangeNotifierProvider(create: (_) => KeuanganProvider()),
        ChangeNotifierProvider(create: (_) => PresensiProvider()),
        ChangeNotifierProvider(create: (_) => AkademikProvider()),
        ChangeNotifierProvider(create: (_) => BantuanProvider()),
      ],
      child: DynamicColorBuilder(
        builder: (ColorScheme? lightDynamic, ColorScheme? darkDynamic) {
          return Consumer<ThemeProvider>(
            builder: (context, themeProvider, _) {
              // Update dynamic schemes globally in AppColors
              AppColors.setDynamicSchemes(lightDynamic, darkDynamic);

              final effectiveLightDynamic =
                  themeProvider.useDynamicColor ? lightDynamic : null;
              final effectiveDarkDynamic =
                  themeProvider.useDynamicColor ? darkDynamic : null;

              return MaterialApp(
                title: 'Wali Murid - MDTHS',
                debugShowCheckedModeBanner: false,
                theme: AppTheme.lightTheme(effectiveLightDynamic),
                darkTheme: AppTheme.darkTheme(effectiveDarkDynamic),
                themeMode: themeProvider.themeMode,
                home: const SplashScreen(),
              );
            },
          );
        },
      ),
    );
  }
}
