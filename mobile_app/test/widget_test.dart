import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:community_report_app/core/network/api_client.dart';
import 'package:community_report_app/core/theme/app_theme.dart';
import 'package:community_report_app/features/auth/data/auth_repository.dart';
import 'package:community_report_app/features/auth/domain/user_model.dart';
import 'package:community_report_app/features/auth/presentation/auth_provider.dart';
import 'package:community_report_app/features/auth/presentation/splash_screen.dart';
import 'package:community_report_app/features/reports/data/report_repository.dart';
import 'package:community_report_app/features/reports/domain/report_models.dart';
import 'package:community_report_app/features/reports/presentation/report_form_screen.dart';
import 'package:community_report_app/features/reports/presentation/report_provider.dart';
import 'package:community_report_app/features/reports/presentation/widgets/report_photo_picker.dart';

class _FakeReportRepository extends ReportRepository {
  int submitCallCount = 0;
  bool shouldFailSubmit = false;

  _FakeReportRepository() : super(ApiClient());

  @override
  Future<List<CategoryModel>> fetchCategories() async {
    return const [
      CategoryModel(
        id: 1,
        name: 'Road Damage & Potholes',
        slug: 'road-damage',
        defaultPriority: 'medium',
        isHazardous: false,
      ),
      CategoryModel(
        id: 2,
        name: 'Downed Electrical Line',
        slug: 'electrical-hazard',
        defaultPriority: 'urgent',
        isHazardous: true,
        safetyAdvisory: 'Keep at least 10 meters away from live wires.',
      ),
    ];
  }

  @override
  Future<({
    List<ReportModel> reports,
    UserStats stats,
    List<CommunityPinModel> communityPins,
  })> fetchMyReports({String status = 'all', String search = ''}) async {
    return (
      reports: const <ReportModel>[],
      stats: const UserStats(),
      communityPins: const <CommunityPinModel>[],
    );
  }

  @override
  Future<ReportModel> submitReport({
    required int categoryId,
    required String title,
    required String description,
    required String priority,
    required double latitude,
    required double longitude,
    required String addressText,
    String? barangay,
    String? landmark,
    bool isHazardWarningAcknowledged = false,
    File? photo,
  }) async {
    submitCallCount++;
    await Future<void>.delayed(const Duration(milliseconds: 50));
    if (shouldFailSubmit) {
      throw const ApiException(
        message: 'Server timeout. Please retry.',
        statusCode: 504,
      );
    }
    return ReportModel(
      id: 101,
      referenceNumber: 'BRGY-2026-0101',
      title: title,
      description: description,
      priority: priority,
      status: 'submitted',
      statusLabel: 'Submitted',
      latitude: latitude,
      longitude: longitude,
      addressText: addressText,
      barangay: barangay ?? 'Poblacion I',
    );
  }
}

class _FakeAuthRepository extends AuthRepository {
  _FakeAuthRepository() : super(ApiClient());

  @override
  Future<({UserModel user, UserStats stats})> fetchProfile() async {
    return (
      user: const UserModel(
        id: 1,
        name: 'Maria Santos',
        email: 'maria.santos@gmail.com',
        role: 'resident',
        barangay: 'Poblacion I',
      ),
      stats: const UserStats(),
    );
  }
}

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

  testWidgets(
    'Report an Issue form validates fields, preserves draft on error, and prevents overflow on small phone & tablet',
    (WidgetTester tester) async {
      final fakeRepo = _FakeReportRepository();
      final reportProvider = ReportProvider(fakeRepo);
      final authProvider = AuthProvider(_FakeAuthRepository());

      // Test on small phone viewport (360x640) first
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
            ChangeNotifierProvider<ReportProvider>.value(value: reportProvider),
          ],
          child: MaterialApp(
            theme: AppTheme.lightTheme,
            home: const Scaffold(
              body: ReportFormBottomSheet(
                isFullScreen: true,
                disableMapTilesForTest: true,
              ),
            ),
          ),
        ),
      );

      await tester.pumpAndSettle();

      // Verify categories loaded from API
      expect(find.text('Road Damage & Potholes'), findsOneWidget);
      expect(find.text('Downed Electrical Line'), findsOneWidget);

      // Select a category chip
      await tester.tap(find.text('Road Damage & Potholes'));
      await tester.pumpAndSettle();

      // Scroll down to submit button and tap without filling required fields
      final submitButton = find.byKey(const Key('submit_report_button'));
      await tester.ensureVisible(submitButton);
      await tester.pumpAndSettle();
      await tester.tap(submitButton);
      await tester.pumpAndSettle();

      // Validation messages must appear and submit should not have fired
      expect(find.text('Enter at least 5 characters'), findsOneWidget);
      expect(find.text('Enter at least 10 characters of detail'), findsOneWidget);
      expect(fakeRepo.submitCallCount, 0);

      // Scroll back up and enter valid summary, description, and street address
      final summaryField = find.widgetWithText(TextFormField, 'Short Summary');
      await tester.ensureVisible(summaryField);
      await tester.pumpAndSettle();
      await tester.enterText(
        summaryField,
        'Deep pothole near Santa Cruz Plaza',
      );
      await tester.enterText(
        find.widgetWithText(TextFormField, 'Description'),
        'Large crater causing tricycles and motorcycles to swerve dangerously.',
      );

      final streetField =
          find.widgetWithText(TextFormField, 'Street / Sitio / Purok');
      await tester.ensureVisible(streetField);
      await tester.pumpAndSettle();
      await tester.enterText(
        streetField,
        'P. Guevara Avenue',
      );

      // Verify draft is kept in memory
      expect(reportProvider.draft.title, 'Deep pothole near Santa Cruz Plaza');
      expect(reportProvider.draft.barangay, 'Poblacion I');

      // Simulate server error on first submit -> draft must remain intact and Retry shown
      fakeRepo.shouldFailSubmit = true;
      await tester.ensureVisible(submitButton);
      await tester.pumpAndSettle();
      await tester.tap(submitButton);
      await tester.pumpAndSettle();

      expect(fakeRepo.submitCallCount, 1);
      expect(find.text('Server timeout. Please retry.'), findsOneWidget);
      expect(find.text('Retry'), findsOneWidget);
      expect(reportProvider.draft.title, 'Deep pothole near Santa Cruz Plaza');

      // Resize to tablet portrait/view (1024x768) -> no overflow, draft still intact
      tester.view.physicalSize = const Size(1024, 768);
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
    },
  );

  testWidgets(
    'Camera button is prominent (>=48dp touch target), visible on small phone without scroll, and handles thumbnail actions',
    (WidgetTester tester) async {
      final fakeRepo = _FakeReportRepository();
      final reportProvider = ReportProvider(fakeRepo);
      final authProvider = AuthProvider(_FakeAuthRepository());

      // Small phone screen: 360 x 640
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
            ChangeNotifierProvider<ReportProvider>.value(value: reportProvider),
          ],
          child: MaterialApp(
            theme: AppTheme.lightTheme,
            home: const Scaffold(
              body: ReportFormBottomSheet(
                isFullScreen: true,
                disableMapTilesForTest: true,
              ),
            ),
          ),
        ),
      );

      await tester.pumpAndSettle();

      // Find Camera and Gallery buttons
      final cameraFinder = find.byKey(const Key('report_camera_button'));
      final galleryFinder = find.byKey(const Key('report_gallery_button'));

      expect(cameraFinder, findsOneWidget);
      expect(galleryFinder, findsOneWidget);

      // Verify camera button is directly visible without any scrolling on 360x640 screen
      expect(tester.getTopLeft(cameraFinder).dy, lessThan(640));

      // Verify touch target height is at least 48dp
      final cameraSize = tester.getSize(cameraFinder);
      expect(cameraSize.height, greaterThanOrEqualTo(48.0));

      final gallerySize = tester.getSize(galleryFinder);
      expect(gallerySize.height, greaterThanOrEqualTo(48.0));

      // Verify initial label and icon
      expect(find.text('Take Photo'), findsOneWidget);
      expect(find.byIcon(Icons.camera_alt_rounded), findsOneWidget);
      expect(find.text('Gallery'), findsOneWidget);

      // Verify tablet size (1024x768) does not throw overflow or render issues
      tester.view.physicalSize = const Size(1024, 768);
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
    },
  );

  testWidgets(
    'ReportPhotoPicker renders thumbnail preview, retake button, and triggers remove callback',
    (WidgetTester tester) async {
      File? currentPhoto = File('test_evidence.jpg');

      await tester.pumpWidget(
        MaterialApp(
          theme: AppTheme.lightTheme,
          home: Scaffold(
            body: StatefulBuilder(
              builder: (context, setState) {
                return ReportPhotoPicker(
                  photo: currentPhoto,
                  onPhotoChanged: (newPhoto) {
                    setState(() {
                      currentPhoto = newPhoto;
                    });
                  },
                );
              },
            ),
          ),
        ),
      );

      await tester.pumpAndSettle();

      // Verify thumbnail preview, badge, and buttons in attached state
      expect(find.text('1/1 Attached'), findsOneWidget);
      expect(find.text('Retake Photo'), findsOneWidget);
      expect(find.text('Replace from Gallery'), findsOneWidget);
      expect(find.byIcon(Icons.delete_outline_rounded), findsOneWidget);

      // Tap remove button
      await tester.tap(find.byIcon(Icons.delete_outline_rounded));
      await tester.pumpAndSettle();

      // Verify photo removed and widget reverts to unattached state
      expect(currentPhoto, isNull);
      expect(find.text('Take Photo'), findsOneWidget);
      expect(find.text('Gallery'), findsOneWidget);
      expect(find.text('1/1 Attached'), findsNothing);
    },
  );
}
