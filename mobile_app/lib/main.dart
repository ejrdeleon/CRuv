import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'core/network/api_client.dart';
import 'core/theme/app_theme.dart';
import 'core/theme/theme_provider.dart';
import 'features/auth/data/auth_repository.dart';
import 'features/auth/presentation/auth_provider.dart';
import 'features/auth/presentation/login_register_screen.dart';
import 'features/auth/presentation/splash_screen.dart';
import 'features/home/presentation/main_shell_screen.dart';
import 'features/reports/data/report_repository.dart';
import 'features/reports/presentation/report_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Lock orientation to portrait-only to ensure consistent layout and UX
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);

  // Load saved ThemeMode before first frame to prevent bright flash in dark mode
  final initialThemeMode = await ThemeProvider.loadSavedThemeMode();

  final apiClient = ApiClient();
  await apiClient.loadCustomBaseUrl();

  final authRepository = AuthRepository(apiClient);
  final reportRepository = ReportRepository(apiClient);

  runApp(
    MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: apiClient),
        ChangeNotifierProvider(
          create: (_) => ThemeProvider(initialThemeMode: initialThemeMode),
        ),
        ChangeNotifierProvider(
          create: (_) => AuthProvider(authRepository)..initializeSession(),
        ),
        ChangeNotifierProvider(
          create: (_) => ReportProvider(reportRepository),
        ),
      ],
      child: const CommunityReportApp(),
    ),
  );
}

class CommunityReportApp extends StatelessWidget {
  const CommunityReportApp({super.key});

  @override
  Widget build(BuildContext context) {
    final themeMode = context.watch<ThemeProvider>().themeMode;

    return MaterialApp(
      title: 'Community Report App',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: themeMode,
      home: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          if (auth.isInitializing) {
            return const SplashScreen();
          }
          if (!auth.isAuthenticated) {
            return const LoginRegisterScreen();
          }
          return const MainShellScreen();
        },
      ),
    );
  }
}
