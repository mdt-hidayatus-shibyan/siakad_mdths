import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'core/storage/storage_service.dart';
import 'core/theme/app_theme.dart';
import 'providers/akademik_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/bantuan_provider.dart';
import 'providers/dashboard_provider.dart';
import 'providers/kas_provider.dart';
import 'providers/laporan_provider.dart';
import 'providers/murid_provider.dart';
import 'providers/nilai_provider.dart';
import 'providers/pelanggaran_provider.dart';
import 'providers/presensi_provider.dart';
import 'providers/presensi_ujian_provider.dart';
import 'providers/tabungan_provider.dart';
import 'providers/tagihan_provider.dart';
import 'providers/theme_provider.dart';
import 'ui/splash/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi awal SharedPreferences & In-Memory Cache
  await StorageService.init();

  // Enforce Android 17 Full Edge-to-Edge System Bar Transparency
  SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      systemNavigationBarColor: Colors.transparent,
      systemNavigationBarContrastEnforced: false,
    ),
  );

  runApp(const MDTHidayatusShibyanApp());
}

class MDTHidayatusShibyanApp extends StatelessWidget {
  const MDTHidayatusShibyanApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
        ChangeNotifierProvider(create: (_) => PresensiProvider()),
        ChangeNotifierProvider(create: (_) => PresensiUjianProvider()),
        ChangeNotifierProvider(create: (_) => PelanggaranProvider()),
        ChangeNotifierProvider(create: (_) => NilaiProvider()),
        ChangeNotifierProvider(create: (_) => KasProvider()),
        ChangeNotifierProvider(create: (_) => TabunganProvider()),
        ChangeNotifierProvider(create: (_) => TagihanProvider()),
        ChangeNotifierProvider(create: (_) => MuridProvider()),
        ChangeNotifierProvider(create: (_) => AkademikProvider()),
        ChangeNotifierProvider(create: (_) => BantuanProvider()),
        ChangeNotifierProvider(create: (_) => LaporanProvider()),
      ],
      child: Consumer<ThemeProvider>(
        builder: (context, themeProvider, _) {
          return MaterialApp(
            title: 'Ustadz MDTHS',
            debugShowCheckedModeBanner: false,
            theme: AppTheme.lightTheme,
            darkTheme: AppTheme.darkTheme,
            themeMode: themeProvider.themeMode,
            home: const SplashScreen(),
          );
        },
      ),
    );
  }
}
