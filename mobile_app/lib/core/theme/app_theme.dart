import 'package:flutter/material.dart';

/// Unified Design Tokens & Material 3 Theme (Shared with Laravel Admin Web).
/// Typography: Inter (with system fallback), heavier weights for metrics & CTAs.
/// Status Tokens: Flat, accessible, solid dot + text label (no neon, no glow, no glass).
class AppTheme {
  AppTheme._();

  // Shared Font Family
  static const String fontFamily = 'Inter';

  // Brand & Surface Tokens (Light Mode Default for Outdoor Sunlight Readability)
  static const Color primaryTeal = Color(0xFF0F766E);
  static const Color primaryTealDark = Color(0xFF115E59);
  static const Color primaryTealLight = Color(0xFFCCFBF1);

  static const Color slate950 = Color(0xFF020617);
  static const Color slate900 = Color(0xFF0F172A);
  static const Color slate800 = Color(0xFF1E293B);
  static const Color slate700 = Color(0xFF334155);
  static const Color slate600 = Color(0xFF475569);
  static const Color slate500 = Color(0xFF64748B);
  static const Color slate400 = Color(0xFF94A3B8);
  static const Color slate300 = Color(0xFFCBD5E1);
  static const Color slate200 = Color(0xFFE2E8F0);
  static const Color slate100 = Color(0xFFF1F5F9);
  static const Color slate50 = Color(0xFFF8FAFC);

  // Shared Accessible Status & Priority Tokens (Matches Admin Web CSS Variables)
  // 1. Low priority: Soft Pink
  static const Color statusLowDot = Color(0xFFDB2777);
  static const Color statusLowBgLight = Color(0xFFFDF2F8);
  static const Color statusLowTextLight = Color(0xFF9D174D);
  static const Color statusLowBgDark = Color(0xFF500724);
  static const Color statusLowTextDark = Color(0xFFF9A8D4);

  // 2. In progress / Pending: Amber
  static const Color statusProgressDot = Color(0xFFD97706);
  static const Color statusProgressBgLight = Color(0xFFFFFBEB);
  static const Color statusProgressTextLight = Color(0xFF92400E);
  static const Color statusProgressBgDark = Color(0xFF451A03);
  static const Color statusProgressTextDark = Color(0xFFFCD34D);

  // 3. Critical hazard / Urgent: Coral Red
  static const Color statusHazardDot = Color(0xFFE11D48);
  static const Color statusHazardBgLight = Color(0xFFFFF1F2);
  static const Color statusHazardTextLight = Color(0xFF9F1239);
  static const Color statusHazardBgDark = Color(0xFF4C0519);
  static const Color statusHazardTextDark = Color(0xFFFDA4AF);

  // 4. Resolved: Green
  static const Color statusResolvedDot = Color(0xFF059669);
  static const Color statusResolvedBgLight = Color(0xFFECFDF5);
  static const Color statusResolvedTextLight = Color(0xFF065F46);
  static const Color statusResolvedBgDark = Color(0xFF022C22);
  static const Color statusResolvedTextDark = Color(0xFF6EE7B7);

  // Semantic Aliases
  static const Color primaryColor = primaryTealDark;
  static const Color backgroundLight = slate50;
  static const Color borderColor = slate200;
  static const Color textPrimary = slate900;
  static const Color textSecondary = slate600;
  static const Color dangerColor = statusHazardDot;
  static const Color warningColor = statusProgressDot;
  static const Color infoColor = Color(0xFF1D4ED8);
  static const Color successColor = statusResolvedDot;

  // Spacing Scale
  static const double s4 = 4.0;
  static const double s8 = 8.0;
  static const double s12 = 12.0;
  static const double s16 = 16.0;
  static const double s20 = 20.0;
  static const double s24 = 24.0;
  static const double s32 = 32.0;

  static const double spacingXs = s8;
  static const double spacingSm = s12;
  static const double spacingMd = s16;
  static const double spacingLg = s24;
  static const double spacingXl = s32;

  // Bento Grid & Component Radii
  static const double r6 = 6.0;
  static const double r8 = 8.0;
  static const double r10 = 10.0;
  static const double r16 = 16.0;

  static const double radiusSm = r8;
  static const double radiusMd = r10;
  static const double radiusBento = r16;

  // Minimum accessible touch target size (48dp)
  static const double minTouchTarget = 48.0;

  static TextTheme _buildTextTheme(Color primaryText, Color secondaryText) {
    return TextTheme(
      headlineMedium: TextStyle(
        fontFamily: fontFamily,
        fontSize: 26,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.6,
        color: primaryText,
      ),
      titleLarge: TextStyle(
        fontFamily: fontFamily,
        fontSize: 19,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.4,
        color: primaryText,
      ),
      titleMedium: TextStyle(
        fontFamily: fontFamily,
        fontSize: 15,
        fontWeight: FontWeight.w700,
        letterSpacing: -0.2,
        color: primaryText,
      ),
      bodyLarge: TextStyle(
        fontFamily: fontFamily,
        fontSize: 15,
        fontWeight: FontWeight.w400,
        height: 1.45,
        color: primaryText,
      ),
      bodyMedium: TextStyle(
        fontFamily: fontFamily,
        fontSize: 13.5,
        fontWeight: FontWeight.w500,
        height: 1.4,
        color: primaryText,
      ),
      bodySmall: TextStyle(
        fontFamily: fontFamily,
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: secondaryText,
      ),
    );
  }

  static ThemeData get lightTheme {
    final colorScheme = ColorScheme.fromSeed(
      seedColor: primaryTeal,
      brightness: Brightness.light,
      primary: primaryTealDark,
      onPrimary: Colors.white,
      secondary: slate800,
      surface: Colors.white,
      onSurface: slate900,
      error: statusHazardDot,
    );

    return ThemeData(
      useMaterial3: true,
      fontFamily: fontFamily,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: slate50,
      textTheme: _buildTextTheme(slate900, slate600),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: slate900,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: fontFamily,
          color: slate900,
          fontSize: 17,
          fontWeight: FontWeight.w800,
          letterSpacing: -0.3,
        ),
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusBento),
          side: const BorderSide(color: slate200, width: 1),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: s12,
          vertical: 14,
        ),
        constraints: const BoxConstraints(minHeight: minTouchTarget),
        labelStyle: const TextStyle(
          fontFamily: fontFamily,
          color: slate700,
          fontSize: 13,
          fontWeight: FontWeight.w600,
        ),
        hintStyle: const TextStyle(
          fontFamily: fontFamily,
          color: slate500,
          fontSize: 13,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(r8),
          borderSide: const BorderSide(color: slate300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(r8),
          borderSide: const BorderSide(color: slate300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(r8),
          borderSide: const BorderSide(color: primaryTealDark, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(r8),
          borderSide: const BorderSide(color: statusHazardDot),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryTealDark,
          foregroundColor: Colors.white,
          elevation: 0,
          minimumSize: const Size.fromHeight(minTouchTarget),
          padding: const EdgeInsets.symmetric(horizontal: s16, vertical: s12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(r8),
          ),
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 14.5,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.1,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: slate900,
          minimumSize: const Size(64, minTouchTarget),
          padding: const EdgeInsets.symmetric(horizontal: s16, vertical: s12),
          side: const BorderSide(color: slate300),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(r8),
          ),
          textStyle: const TextStyle(
            fontFamily: fontFamily,
            fontSize: 13.5,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        indicatorColor: primaryTealLight,
        surfaceTintColor: Colors.transparent,
        height: 64,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          final isSelected = states.contains(WidgetState.selected);
          return TextStyle(
            fontFamily: fontFamily,
            fontSize: 11.5,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? primaryTealDark : slate600,
          );
        }),
      ),
      dividerTheme: const DividerThemeData(
        color: slate200,
        thickness: 1,
        space: 1,
      ),
    );
  }

  static ThemeData get darkTheme {
    final colorScheme = ColorScheme.fromSeed(
      seedColor: primaryTeal,
      brightness: Brightness.dark,
      primary: const Color(0xFF14B8A6),
      onPrimary: slate950,
      surface: slate900,
      onSurface: slate50,
      error: statusHazardDot,
    );

    return ThemeData(
      useMaterial3: true,
      fontFamily: fontFamily,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: slate950,
      textTheme: _buildTextTheme(slate50, slate400),
      appBarTheme: const AppBarTheme(
        backgroundColor: slate900,
        foregroundColor: slate50,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: fontFamily,
          color: slate50,
          fontSize: 17,
          fontWeight: FontWeight.w800,
          letterSpacing: -0.3,
        ),
      ),
      cardTheme: CardThemeData(
        color: slate900,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusBento),
          side: const BorderSide(color: slate800, width: 1),
        ),
      ),
    );
  }

  /// Resolves the shared flat status/priority token (dot color + accessible background/text).
  static ({Color dot, Color bg, Color fg, String label}) resolveStatusToken({
    required String status,
    String? priority,
    bool isDark = false,
  }) {
    if (status == 'resolved') {
      return (
        dot: statusResolvedDot,
        bg: isDark ? statusResolvedBgDark : statusResolvedBgLight,
        fg: isDark ? statusResolvedTextDark : statusResolvedTextLight,
        label: 'Resolved',
      );
    }
    if (status == 'rejected' ||
        status == 'duplicate' ||
        priority == 'urgent') {
      final label = status == 'rejected'
          ? 'Rejected'
          : (status == 'duplicate' ? 'Duplicate' : 'Critical Hazard');
      return (
        dot: statusHazardDot,
        bg: isDark ? statusHazardBgDark : statusHazardBgLight,
        fg: isDark ? statusHazardTextDark : statusHazardTextLight,
        label: label,
      );
    }
    if (priority == 'low' && status == 'submitted') {
      return (
        dot: statusLowDot,
        bg: isDark ? statusLowBgDark : statusLowBgLight,
        fg: isDark ? statusLowTextDark : statusLowTextLight,
        label: 'Low Priority',
      );
    }
    // Default active states (submitted, under_review, verified, assigned, in_progress) -> Amber
    final labelMap = {
      'submitted': 'Submitted',
      'under_review': 'Under Review',
      'verified': 'Verified',
      'assigned': 'Assigned',
      'in_progress': 'In Progress',
    };
    return (
      dot: statusProgressDot,
      bg: isDark ? statusProgressBgDark : statusProgressBgLight,
      fg: isDark ? statusProgressTextDark : statusProgressTextLight,
      label: labelMap[status] ?? 'In Progress',
    );
  }

  /// Resolves priority token specifically (Low -> Soft Pink, Medium/High -> Amber, Urgent -> Coral Red).
  static ({Color dot, Color bg, Color fg, String label}) resolvePriorityToken(
    String priority, {
    bool isDark = false,
  }) {
    switch (priority) {
      case 'urgent':
        return (
          dot: statusHazardDot,
          bg: isDark ? statusHazardBgDark : statusHazardBgLight,
          fg: isDark ? statusHazardTextDark : statusHazardTextLight,
          label: 'Critical Hazard',
        );
      case 'high':
      case 'medium':
        return (
          dot: statusProgressDot,
          bg: isDark ? statusProgressBgDark : statusProgressBgLight,
          fg: isDark ? statusProgressTextDark : statusProgressTextLight,
          label: priority == 'high' ? 'High Priority' : 'Medium Priority',
        );
      default:
        return (
          dot: statusLowDot,
          bg: isDark ? statusLowBgDark : statusLowBgLight,
          fg: isDark ? statusLowTextDark : statusLowTextLight,
          label: 'Low Priority',
        );
    }
  }
}

/// Shared flat status indicator widget: solid dot + text label (no glow, no blur).
class StatusPill extends StatelessWidget {
  final String status;
  final String? customLabel;
  final String? priority;

  const StatusPill({
    super.key,
    required this.status,
    this.customLabel,
    this.priority,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final token = AppTheme.resolveStatusToken(
      status: status,
      priority: priority,
      isDark: isDark,
    );

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: token.bg,
        borderRadius: BorderRadius.circular(AppTheme.r6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(
              color: token.dot,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 6),
          Text(
            customLabel ?? token.label,
            style: TextStyle(
              fontFamily: AppTheme.fontFamily,
              color: token.fg,
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

/// Shared flat priority indicator widget: solid dot + text label.
class PriorityPill extends StatelessWidget {
  final String priority;

  const PriorityPill({super.key, required this.priority});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final token = AppTheme.resolvePriorityToken(priority, isDark: isDark);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: token.bg,
        borderRadius: BorderRadius.circular(AppTheme.r6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(
              color: token.dot,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 6),
          Text(
            token.label,
            style: TextStyle(
              fontFamily: AppTheme.fontFamily,
              color: token.fg,
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
