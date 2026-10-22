import 'dart:io';
import 'package:flutter/foundation.dart';
import '../../../core/network/api_client.dart';
import '../../auth/domain/user_model.dart';
import '../data/report_repository.dart';
import '../domain/report_models.dart';

class ReportProvider extends ChangeNotifier {
  final ReportRepository _repository;

  List<CategoryModel> _categories = [];
  List<ReportModel> _reports = [];
  List<CommunityPinModel> _communityPins = [];
  ReportModel? _selectedReport;
  UserStats _stats = const UserStats();

  String _statusFilter = 'all';
  String _searchQuery = '';

  bool _isLoadingList = false;
  bool _isLoadingDetail = false;
  bool _isSubmitting = false;
  bool _isOffline = false;
  String? _errorMessage;

  ReportProvider(this._repository);

  List<CategoryModel> get categories => _categories;
  List<ReportModel> get reports => _reports;
  List<CommunityPinModel> get communityPins => _communityPins;
  ReportModel? get selectedReport => _selectedReport;
  UserStats get stats => _stats;
  String get statusFilter => _statusFilter;
  String get searchQuery => _searchQuery;
  bool get isLoadingList => _isLoadingList;
  bool get isLoadingDetail => _isLoadingDetail;
  bool get isSubmitting => _isSubmitting;
  bool get isOffline => _isOffline;
  String? get errorMessage => _errorMessage;

  Future<void> loadInitialData() async {
    await Future.wait([
      loadCategories(),
      loadMyReports(),
    ]);
  }

  Future<void> loadCategories() async {
    try {
      _categories = await _repository.fetchCategories();
      notifyListeners();
    } catch (_) {
      // Categories will be retried when opening the report sheet if empty.
    }
  }

  Future<void> loadMyReports({
    String? status,
    String? search,
  }) async {
    if (status != null) {
      _statusFilter = status;
    }
    if (search != null) {
      _searchQuery = search;
    }

    _isLoadingList = true;
    _errorMessage = null;
    _isOffline = false;
    notifyListeners();

    try {
      final result = await _repository.fetchMyReports(
        status: _statusFilter,
        search: _searchQuery,
      );
      _reports = result.reports;
      _stats = result.stats;
      _communityPins = result.communityPins;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isOffline = e.isOffline;
    } catch (_) {
      _errorMessage = 'Could not load your reports. Please try again.';
    } finally {
      _isLoadingList = false;
      notifyListeners();
    }
  }

  Future<void> loadReportDetail(int reportId) async {
    _isLoadingDetail = true;
    _errorMessage = null;
    _isOffline = false;
    notifyListeners();

    try {
      _selectedReport = await _repository.fetchReportDetail(reportId);
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _isOffline = e.isOffline;
    } catch (_) {
      _errorMessage = 'Could not load report details.';
    } finally {
      _isLoadingDetail = false;
      notifyListeners();
    }
  }

  Future<ReportModel?> submitReport({
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
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final created = await _repository.submitReport(
        categoryId: categoryId,
        title: title,
        description: description,
        priority: priority,
        latitude: latitude,
        longitude: longitude,
        addressText: addressText,
        barangay: barangay,
        landmark: landmark,
        isHazardWarningAcknowledged: isHazardWarningAcknowledged,
        photo: photo,
      );
      await loadMyReports();
      return created;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return null;
    } catch (_) {
      _errorMessage = 'Failed to submit report. Please try again.';
      return null;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }
}
