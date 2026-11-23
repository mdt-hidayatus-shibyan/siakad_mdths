import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/akademik_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/dashboard_provider.dart';
import '../../providers/kas_provider.dart';
import '../../providers/murid_provider.dart';
import '../../providers/nilai_provider.dart';
import '../../providers/pelanggaran_provider.dart';
import '../../providers/presensi_provider.dart';
import '../../providers/presensi_ujian_provider.dart';
import '../../providers/syarat_ujian_provider.dart';
import '../../providers/tagihan_provider.dart';
import '../../ui/auth/login_screen.dart';
import '../services/bell_service.dart';
import '../storage/storage_service.dart';

class SessionHelper {
  SessionHelper._();

  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();
  static final GlobalKey<ScaffoldMessengerState> scaffoldMessengerKey =
      GlobalKey<ScaffoldMessengerState>();

  static bool _isHandlingSessionExpired = false;

  /// Status apakah penanganan sesi habis / logout otomatis sedang berjalan
  static bool get isHandlingSessionExpired => _isHandlingSessionExpired;

  /// Reset flag setelah notifikasi sesi habis ditutup atau saat login ulang
  static void resetSessionExpiredFlag() {
    _isHandlingSessionExpired = false;
  }

  static void resetAllProviders(BuildContext context) {
    try {
      context.read<AuthProvider>().reset();
    } catch (_) {}
    try {
      context.read<DashboardProvider>().reset();
    } catch (_) {}
    try {
      context.read<PresensiProvider>().reset();
    } catch (_) {}
    try {
      context.read<PresensiUjianProvider>().reset();
    } catch (_) {}
    try {
      context.read<SyaratUjianProvider>().reset();
    } catch (_) {}
    try {
      context.read<PelanggaranProvider>().reset();
    } catch (_) {}
    try {
      context.read<NilaiProvider>().reset();
    } catch (_) {}
    try {
      context.read<KasProvider>().reset();
    } catch (_) {}
    try {
      context.read<MuridProvider>().reset();
    } catch (_) {}
    try {
      context.read<AkademikProvider>().reset();
    } catch (_) {}
    try {
      context.read<TagihanProvider>().reset();
    } catch (_) {}
  }

  /// Menangani token login yang sudah habis / tidak valid (HTTP 401):
  /// 1. Cegah eksekusi ganda jika beberapa request paralel gagal bersamaan
  /// 2. Hapus token dan cache akun lokal
  /// 3. Hentikan jadwal pengingat bel mengajar
  /// 4. Reset state seluruh provider
  /// 5. Arahkan otomatis ke LoginScreen dengan pesan pemberitahuan
  static Future<void> handleSessionExpired({String? message}) async {
    if (_isHandlingSessionExpired) return;
    _isHandlingSessionExpired = true;

    final token =
        StorageService.getCachedToken() ?? await StorageService.getToken();
    if (token == null || token.isEmpty) {
      _isHandlingSessionExpired = false;
      return;
    }

    try {
      await StorageService.clearAuth();

      try {
        await BellService.instance.clearTeachingSchedule();
      } catch (_) {}

      final expiredMessage =
          message ??
          'Masa berlaku sesi login Anda telah berakhir demi keamanan. Silakan masuk kembali ke akun Ustadz Anda.';

      void redirectToLogin() {
        final currentContext = navigatorKey.currentContext;
        if (currentContext != null) {
          resetAllProviders(currentContext);
        }

        final navState = navigatorKey.currentState;
        if (navState != null) {
          navState.pushAndRemoveUntil(
            MaterialPageRoute(
              builder: (_) =>
                  LoginScreen(sessionExpiredMessage: expiredMessage),
            ),
            (route) => false,
          );
        } else {
          _isHandlingSessionExpired = false;
        }
      }

      WidgetsBinding.instance.addPostFrameCallback((_) {
        redirectToLogin();
      });
    } catch (_) {
      _isHandlingSessionExpired = false;
    }
  }
}
