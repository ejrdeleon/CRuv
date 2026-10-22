import 'dart:io';
import '../../../core/network/api_client.dart';
import '../../auth/domain/user_model.dart';
import '../domain/report_models.dart';

class ReportRepository {
  final ApiClient _apiClient;

  ReportRepository(this._apiClient);

  Future<List<CategoryModel>> fetchCategories() async {
    final response = await _apiClient.get('/categories');
    final list = response['data'] as List<dynamic>? ?? [];
    return list
        .map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<({
    List<ReportModel> reports,
    UserStats stats,
    List<CommunityPinModel> communityPins,
  })> fetchMyReports({
    String status = 'all',
    String search = '',
  }) async {
    final response = await _apiClient.get(
      '/my-reports',
      queryParameters: {
        if (status != 'all') 'status': status,
        if (search.trim().isNotEmpty) 'search': search.trim(),
      },
    );

    final list = response['data'] as List<dynamic>? ?? [];
    final reports = list
        .map((e) => ReportModel.fromJson(e as Map<String, dynamic>))
        .toList();

    final meta = response['meta'] as Map<String, dynamic>?;
    final stats = UserStats.fromJson(meta?['stats'] as Map<String, dynamic>?);
    final pinsList = meta?['community_pins'] as List<dynamic>? ?? [];
    final communityPins = pinsList
        .map((e) => CommunityPinModel.fromJson(e as Map<String, dynamic>))
        .toList();

    return (
      reports: reports,
      stats: stats,
      communityPins: communityPins,
    );
  }

  Future<ReportModel> fetchReportDetail(int reportId) async {
    final response = await _apiClient.get('/reports/$reportId');
    return ReportModel.fromJson(response['data'] as Map<String, dynamic>);
  }

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
    final fields = <String, String>{
      'category_id': categoryId.toString(),
      'title': title.trim(),
      'description': description.trim(),
      'priority': priority,
      'latitude': latitude.toStringAsFixed(6),
      'longitude': longitude.toStringAsFixed(6),
      'address_text': addressText.trim(),
      'is_hazard_warning_acknowledged':
          isHazardWarningAcknowledged ? '1' : '0',
    };

    if (barangay != null && barangay.trim().isNotEmpty) {
      fields['barangay'] = barangay.trim();
    }
    if (landmark != null && landmark.trim().isNotEmpty) {
      fields['landmark'] = landmark.trim();
    }

    final response = await _apiClient.postMultipart(
      '/reports',
      fields: fields,
      filePath: photo?.path,
      fileField: 'photo',
    );

    return ReportModel.fromJson(response['data'] as Map<String, dynamic>);
  }
}
