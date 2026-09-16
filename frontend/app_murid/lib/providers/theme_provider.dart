import 'package:flutter/material.dart';
import '../core/storage/storage_service.dart';
import '../core/theme/app_colors.dart';

class ThemeProvider extends ChangeNotifier {
  ThemeMode _themeMode = ThemeMode.light; // Default Light Mode
  String _colorPresetKey = 'emerald';

  ThemeMode get themeMode => _themeMode;
  bool get isDarkMode => _themeMode == ThemeMode.dark;
  String get colorPresetKey => _colorPresetKey;
  AppColorPreset get activePreset => AppColors.activePreset;

  ThemeProvider() {
    final cachedPreset = StorageService.getCachedColorPreset();
    if (cachedPreset != null) {
      _colorPresetKey = cachedPreset;
      AppColors.setPreset(cachedPreset);
    }
    _loadTheme();
  }

  Future<void> _loadTheme() async {
    final savedMode = StorageService.getThemeMode();
    if (savedMode == 'dark') {
      _themeMode = ThemeMode.dark;
    } else if (savedMode == 'system') {
      _themeMode = ThemeMode.system;
    } else {
      _themeMode = ThemeMode.light; // Default Light Mode
    }

    final savedPreset = StorageService.getColorPreset();
    if (savedPreset != null && savedPreset != _colorPresetKey) {
      _colorPresetKey = savedPreset;
      AppColors.setPreset(savedPreset);
    }

    notifyListeners();
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    _themeMode = mode;
    notifyListeners();
    if (mode == ThemeMode.dark) {
      await StorageService.setThemeMode('dark');
    } else if (mode == ThemeMode.light) {
      await StorageService.setThemeMode('light');
    } else {
      await StorageService.setThemeMode('system');
    }
  }

  void toggleTheme() {
    if (_themeMode == ThemeMode.dark) {
      setThemeMode(ThemeMode.light);
    } else {
      setThemeMode(ThemeMode.dark);
    }
  }

  Future<void> setColorPreset(String presetKey) async {
    _colorPresetKey = presetKey;
    AppColors.setPreset(presetKey);
    notifyListeners();
    await StorageService.setColorPreset(presetKey);
  }
}
