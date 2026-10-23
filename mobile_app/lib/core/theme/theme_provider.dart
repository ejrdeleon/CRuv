import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Manages and persists in-app theme selection across sessions (System, Light, Dark).
class ThemeProvider extends ChangeNotifier {
  static const String _prefKey = 'app_theme_mode';
  ThemeMode _themeMode;

  ThemeProvider({ThemeMode initialThemeMode = ThemeMode.system})
      : _themeMode = initialThemeMode;

  ThemeMode get themeMode => _themeMode;

  /// Loads the persisted theme mode synchronously before the first frame runs.
  static Future<ThemeMode> loadSavedThemeMode() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final saved = prefs.getString(_prefKey);
      if (saved == 'light') return ThemeMode.light;
      if (saved == 'dark') return ThemeMode.dark;
      return ThemeMode.system;
    } catch (_) {
      return ThemeMode.system;
    }
  }

  /// Updates and persists the selected ThemeMode.
  Future<void> setThemeMode(ThemeMode mode) async {
    if (_themeMode == mode) return;
    _themeMode = mode;
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_prefKey, mode.name);
    } catch (_) {}
  }
}
