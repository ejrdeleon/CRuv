import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

/// Structured exception thrown when an API request fails or the device is offline.
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;
  final bool isOffline;

  const ApiException({
    required this.message,
    this.statusCode,
    this.fieldErrors = const {},
    this.isOffline = false,
  });

  /// Returns a combined user-friendly validation or error message.
  String get detailedMessage {
    if (fieldErrors.isNotEmpty) {
      final firstList = fieldErrors.values.first;
      if (firstList.isNotEmpty) {
        return firstList.first;
      }
    }
    return message;
  }

  @override
  String toString() => detailedMessage;
}

/// Centralized HTTP client managing Sanctum Bearer tokens and JSON/Multipart requests.
class ApiClient {
  static const String _tokenKey = 'sanctum_auth_token';
  static const String _customBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const String _serverUrlKey = 'custom_server_base_url';

  final FlutterSecureStorage _storage;
  final http.Client _httpClient;
  String? _cachedBaseUrl;

  ApiClient({
    FlutterSecureStorage? storage,
    http.Client? httpClient,
  })  : _storage = storage ?? const FlutterSecureStorage(),
        _httpClient = httpClient ?? http.Client();

  /// Resolves the Laravel backend URL automatically.
  /// 1. Custom URL saved by user in-app
  /// 2. Build-time --dart-define=API_BASE_URL
  /// 3. Physical phone over Wi-Fi / reversed loopback: 127.0.0.1 or 192.168.1.7
  String get baseUrl {
    if (_cachedBaseUrl != null && _cachedBaseUrl!.isNotEmpty) {
      return _cachedBaseUrl!;
    }
    if (_customBaseUrl.isNotEmpty) {
      return _customBaseUrl;
    }
    // Using 127.0.0.1 works seamlessly with 'adb reverse tcp:8000 tcp:8000' on Android
    // and directly on Windows desktop/web/iOS.
    return 'http://127.0.0.1:8000/api/v1';
  }

  Future<void> loadCustomBaseUrl() async {
    final saved = await _storage.read(key: _serverUrlKey);
    if (saved != null && saved.trim().isNotEmpty) {
      _cachedBaseUrl = saved.trim();
    }
  }

  Future<void> setCustomBaseUrl(String url) async {
    final clean = url.trim().replaceAll(RegExp(r'/+$'), '');
    _cachedBaseUrl = clean;
    await _storage.write(key: _serverUrlKey, value: clean);
  }

  Future<String?> getToken() => _storage.read(key: _tokenKey);

  Future<void> saveToken(String token) =>
      _storage.write(key: _tokenKey, value: token);

  Future<void> clearToken() => _storage.delete(key: _tokenKey);

  Future<Map<String, String>> _buildHeaders({bool isJson = true}) async {
    final token = await getToken();
    return {
      'Accept': 'application/json',
      if (isJson) 'Content-Type': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }

  Uri _buildUri(String path, [Map<String, String>? queryParameters]) {
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('$baseUrl$normalizedPath');
    if (queryParameters != null && queryParameters.isNotEmpty) {
      return uri.replace(queryParameters: queryParameters);
    }
    return uri;
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? queryParameters,
  }) async {
    try {
      final headers = await _buildHeaders();
      final response = await _httpClient
          .get(_buildUri(path, queryParameters), headers: headers)
          .timeout(const Duration(seconds: 15));
      return _parseResponse(response);
    } on SocketException {
      throw const ApiException(
        message:
            'Unable to connect to the barangay server. Please check your internet or Wi-Fi connection.',
        isOffline: true,
      );
    } on TimeoutException {
      throw const ApiException(
        message: 'The server took too long to respond. Please try again.',
        isOffline: true,
      );
    }
  }

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final headers = await _buildHeaders();
      final response = await _httpClient
          .post(
            _buildUri(path),
            headers: headers,
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(const Duration(seconds: 15));
      return _parseResponse(response);
    } on SocketException {
      throw const ApiException(
        message:
            'Unable to connect to the barangay server. Please check your connection.',
        isOffline: true,
      );
    } on TimeoutException {
      throw const ApiException(
        message: 'Connection timed out. Please try again.',
        isOffline: true,
      );
    }
  }

  Future<Map<String, dynamic>> put(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final headers = await _buildHeaders();
      final response = await _httpClient
          .put(
            _buildUri(path),
            headers: headers,
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(const Duration(seconds: 15));
      return _parseResponse(response);
    } on SocketException {
      throw const ApiException(
        message:
            'Unable to connect to the barangay server. Please check your connection.',
        isOffline: true,
      );
    } on TimeoutException {
      throw const ApiException(
        message: 'Connection timed out. Please try again.',
        isOffline: true,
      );
    }
  }

  /// Sends a multipart POST request for report submission with photo evidence.
  Future<Map<String, dynamic>> postMultipart(
    String path, {
    required Map<String, String> fields,
    String? fileField,
    String? filePath,
  }) async {
    try {
      final headers = await _buildHeaders(isJson: false);
      final request = http.MultipartRequest('POST', _buildUri(path));
      request.headers.addAll(headers);
      request.fields.addAll(fields);

      if (fileField != null && filePath != null && filePath.isNotEmpty) {
        request.files.add(
          await http.MultipartFile.fromPath(fileField, filePath),
        );
      }

      final streamedResponse =
          await request.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamedResponse);
      return _parseResponse(response);
    } on SocketException {
      throw const ApiException(
        message:
            'Cannot upload report while offline. Please verify your connection and retry.',
        isOffline: true,
      );
    } on TimeoutException {
      throw const ApiException(
        message: 'Photo upload timed out. Please try again.',
        isOffline: true,
      );
    }
  }

  Map<String, dynamic> _parseResponse(http.Response response) {
    Map<String, dynamic> decoded = {};
    if (response.body.isNotEmpty) {
      try {
        final raw = jsonDecode(response.body);
        if (raw is Map<String, dynamic>) {
          decoded = raw;
        }
      } catch (_) {
        decoded = {'message': 'Unexpected response from server.'};
      }
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    final message = (decoded['message'] as String?) ??
        'Request failed (${response.statusCode}).';
    final Map<String, List<String>> fieldErrors = {};

    if (decoded['errors'] is Map) {
      final rawErrors = decoded['errors'] as Map;
      rawErrors.forEach((key, value) {
        if (value is List) {
          fieldErrors[key.toString()] =
              value.map((item) => item.toString()).toList();
        }
      });
    }

    throw ApiException(
      message: message,
      statusCode: response.statusCode,
      fieldErrors: fieldErrors,
    );
  }
}
