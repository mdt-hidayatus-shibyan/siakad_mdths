import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'auth_user';
  static const String _keyTheme = 'app_theme_mode';
  static const String _keyColorPreset = 'app_color_preset';
  static const String _keyBaseUrl = 'custom_base_url';
  static const String _keyMenuSizes = 'quick_menu_tile_sizes';
  static const String _keyMenuOrder = 'quick_menu_tile_order';
  static const String _keyPinnedMenus = 'quick_menu_pinned_ids';

  static SharedPreferences? _prefs;
  static String? _cachedToken;
  static Map<String, dynamic>? _cachedUser;
  static String? _cachedTheme;
  static String? _cachedColorPreset;
  static String? _cachedBaseUrl;
  static Map<String, String>? _cachedMenuSizes;
  static Map<String, List<String>>? _cachedMenuOrder;
  static List<String>? _cachedPinnedMenus;

  /// Inisialisasi awal SharedPreferences & populate in-memory cache saat app start
  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
    _cachedToken = _prefs?.getString(_keyToken);
    _cachedTheme = _prefs?.getString(_keyTheme);
    _cachedColorPreset = _prefs?.getString(_keyColorPreset);
    _cachedBaseUrl = _prefs?.getString(_keyBaseUrl);

    final pinnedStr = _prefs?.getString(_keyPinnedMenus);
    if (pinnedStr != null) {
      try {
        final decoded = (jsonDecode(pinnedStr) as List<dynamic>).map((e) => e.toString()).toList();
        _cachedPinnedMenus = decoded;
      } catch (_) {
        _cachedPinnedMenus = null;
      }
    }

    final menuSizesStr = _prefs?.getString(_keyMenuSizes);
    if (menuSizesStr != null) {
      try {
        final decoded = jsonDecode(menuSizesStr) as Map<String, dynamic>;
        _cachedMenuSizes = decoded.map((k, v) => MapEntry(k, v.toString()));
      } catch (_) {
        _cachedMenuSizes = null;
      }
    }

    final userStr = _prefs?.getString(_keyUser);
    if (userStr != null) {
      try {
        _cachedUser = jsonDecode(userStr) as Map<String, dynamic>;
      } catch (_) {
        _cachedUser = null;
      }
    }
  }

  static Future<SharedPreferences> _getPrefs() async {
    return _prefs ??= await SharedPreferences.getInstance();
  }

  // --- TOKEN ---
  static Future<void> saveToken(String token) async {
    _cachedToken = token;
    final prefs = await _getPrefs();
    await prefs.setString(_keyToken, token);
  }

  static Future<String?> getToken() async {
    if (_cachedToken != null) return _cachedToken;
    final prefs = await _getPrefs();
    _cachedToken = prefs.getString(_keyToken);
    return _cachedToken;
  }

  static String? getCachedToken() => _cachedToken;

  // --- USER DATA ---
  static Future<void> saveUser(Map<String, dynamic> userMap) async {
    _cachedUser = userMap;
    final prefs = await _getPrefs();
    await prefs.setString(_keyUser, jsonEncode(userMap));
  }

  static Future<Map<String, dynamic>?> getUser() async {
    if (_cachedUser != null) return _cachedUser;
    final prefs = await _getPrefs();
    final userStr = prefs.getString(_keyUser);
    if (userStr == null) return null;
    try {
      _cachedUser = jsonDecode(userStr) as Map<String, dynamic>;
      return _cachedUser;
    } catch (_) {
      return null;
    }
  }

  static Map<String, dynamic>? getCachedUser() => _cachedUser;

  static Future<void> clearAuth() async {
    _cachedToken = null;
    _cachedUser = null;
    final prefs = await _getPrefs();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
  }

  // --- THEME ---
  static Future<void> saveThemeMode(String mode) async {
    _cachedTheme = mode;
    final prefs = await _getPrefs();
    await prefs.setString(_keyTheme, mode);
  }

  static Future<String?> getThemeMode() async {
    if (_cachedTheme != null) return _cachedTheme;
    final prefs = await _getPrefs();
    _cachedTheme = prefs.getString(_keyTheme);
    return _cachedTheme;
  }

  // --- COLOR PRESET ---
  static Future<void> saveColorPreset(String presetKey) async {
    _cachedColorPreset = presetKey;
    final prefs = await _getPrefs();
    await prefs.setString(_keyColorPreset, presetKey);
  }

  static Future<String?> getColorPreset() async {
    if (_cachedColorPreset != null) return _cachedColorPreset;
    final prefs = await _getPrefs();
    _cachedColorPreset = prefs.getString(_keyColorPreset);
    return _cachedColorPreset;
  }

  static String? getCachedColorPreset() => _cachedColorPreset;

  // --- BASE URL ---
  static Future<void> saveBaseUrl(String url) async {
    _cachedBaseUrl = url;
    final prefs = await _getPrefs();
    await prefs.setString(_keyBaseUrl, url);
  }

  static Future<String?> getBaseUrl() async {
    if (_cachedBaseUrl != null) return _cachedBaseUrl;
    final prefs = await _getPrefs();
    _cachedBaseUrl = prefs.getString(_keyBaseUrl);
    return _cachedBaseUrl;
  }

  static String? getCachedBaseUrl() => _cachedBaseUrl;

  // --- MENU TILE SIZES ---
  static Future<void> saveMenuSizes(Map<String, String> sizes) async {
    _cachedMenuSizes = Map<String, String>.from(sizes);
    final prefs = await _getPrefs();
    await prefs.setString(_keyMenuSizes, jsonEncode(sizes));
  }

  static Map<String, String> getMenuSizes() {
    if (_cachedMenuSizes != null) {
      return Map<String, String>.from(_cachedMenuSizes!);
    }
    final str = _prefs?.getString(_keyMenuSizes);
    if (str != null) {
      try {
        final decoded = jsonDecode(str) as Map<String, dynamic>;
        _cachedMenuSizes = decoded.map((k, v) => MapEntry(k, v.toString()));
        return Map<String, String>.from(_cachedMenuSizes!);
      } catch (_) {}
    }
    return {};
  }

  static Future<void> clearMenuSizes() async {
    _cachedMenuSizes = null;
    final prefs = await _getPrefs();
    await prefs.remove(_keyMenuSizes);
  }

  // --- MENU ORDER ---
  static Future<void> saveMenuOrder(String sectionKey, List<String> order) async {
    _cachedMenuOrder ??= {};
    _cachedMenuOrder![sectionKey] = List<String>.from(order);
    final prefs = await _getPrefs();
    await prefs.setString('${_keyMenuOrder}_$sectionKey', jsonEncode(order));
  }

  static List<String>? getMenuOrder(String sectionKey) {
    if (_cachedMenuOrder != null && _cachedMenuOrder!.containsKey(sectionKey)) {
      return List<String>.from(_cachedMenuOrder![sectionKey]!);
    }
    final str = _prefs?.getString('${_keyMenuOrder}_$sectionKey');
    if (str != null) {
      try {
        final decoded = (jsonDecode(str) as List<dynamic>).map((e) => e.toString()).toList();
        _cachedMenuOrder ??= {};
        _cachedMenuOrder![sectionKey] = decoded;
        return List<String>.from(decoded);
      } catch (_) {}
    }
    return null;
  }

  static Future<void> clearMenuOrder() async {
    _cachedMenuOrder = null;
    final prefs = await _getPrefs();
    for (final sec in ['cepat', 'wali', 'imni']) {
      await prefs.remove('${_keyMenuOrder}_$sec');
    }
  }

  // --- PINNED MENUS ---
  static Future<void> savePinnedMenus(List<String> pinnedIds) async {
    _cachedPinnedMenus = List<String>.from(pinnedIds);
    final prefs = await _getPrefs();
    await prefs.setString(_keyPinnedMenus, jsonEncode(pinnedIds));
  }

  static List<String> getPinnedMenus() {
    if (_cachedPinnedMenus != null) {
      return List<String>.from(_cachedPinnedMenus!);
    }
    final str = _prefs?.getString(_keyPinnedMenus);
    if (str != null) {
      try {
        final decoded = (jsonDecode(str) as List<dynamic>).map((e) => e.toString()).toList();
        _cachedPinnedMenus = decoded;
        return List<String>.from(decoded);
      } catch (_) {}
    }
    return [];
  }

  static Future<void> clearPinnedMenus() async {
    _cachedPinnedMenus = null;
    final prefs = await _getPrefs();
    await prefs.remove(_keyPinnedMenus);
  }
}
