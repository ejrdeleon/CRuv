import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:community_report_app/core/theme/app_theme.dart';
import 'package:community_report_app/features/auth/presentation/splash_screen.dart';

void main() {
  testWidgets('Splash screen and shared StatusPill tokens render accurately', (
    WidgetTester tester,
  ) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.lightTheme,
        home: const Scaffold(
          body: Column(
            children: [
              Expanded(child: SplashScreen()),
              StatusPill(status: 'in_progress'),
              StatusPill(status: 'resolved'),
              PriorityPill(priority: 'low'),
              PriorityPill(priority: 'urgent'),
            ],
          ),
        ),
      ),
    );

    expect(find.text('Community Report App'), findsOneWidget);
    expect(find.text('In Progress'), findsOneWidget);
    expect(find.text('Resolved'), findsOneWidget);
    expect(find.text('Low Priority'), findsOneWidget);
    expect(find.text('Critical Hazard'), findsOneWidget);
  });
}
