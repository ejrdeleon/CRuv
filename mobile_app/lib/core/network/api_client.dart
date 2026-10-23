import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
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
/// Includes automatic multi-host failover across Wi-Fi LAN, Android Emulator (10.0.2.2),
/// and localhost (127.0.0.1) so `php artisan serve` works out of the box.
class ApiClient {
  static const String _tokenKey = 'sanctum_auth_token';
  static const String _customBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const String _serverUrlKey = 'custom_server_base_url';

  /// Candidate backend URLs in priority order for automatic failover when `php artisan serve` is running.
  static const List<String> fallbackBaseUrls = [
    'http://192.168.1.7:8000/api/v1',
    'http://10.0.2.2:8000/api/v1',
    'http://127.0.0.1:8000/api/v1',
  ];

  final FlutterSecureStorage _storage;
  final http.Client _httpClient;
  final bool _enableAutoFailover;
  String? _cachedBaseUrl;

  ApiClient({
    FlutterSecureStorage? storage,
    http.Client? httpClient,
    bool? enableAutoFailover,
  })  : _storage = storage ?? const FlutterSecureStorage(),
        _httpClient = httpClient ?? http.Client(),
        // Disable auto-failover when a mock httpClient is injected in unit/widget tests
        _enableAutoFailover = enableAutoFailover ?? (httpClient == null);

  /// Resolves the Laravel backend URL automatically.
  String get baseUrl {
    if (_cachedBaseUrl != null && _cachedBaseUrl!.isNotEmpty) {
      return _cachedBaseUrl!;
    }
    if (_customBaseUrl.isNotEmpty) {
      return _customBaseUrl;
    }
    if (!kIsWeb && Platform.isAndroid) {
      // Default to LAN Wi-Fi IP first on Android (with automatic failover to 10.0.2.2 and 127.0.0.1)
      return 'http://192.168.1.7:8000/api/v1';
    }
    return 'http://127.0.0.1:8000/api/v1';
  }

  List<String> _candidateBaseUrls() {
    final primary = baseUrl;
    if (!_enableAutoFailover) {
      return [primary];
    }
    final candidates = <String>[primary];
    for (final url in fallbackBaseUrls) {
      if (!candidates.contains(url)) {
        candidates.add(url);
      }
    }
    return candidates;
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
      'Accept-Encoding': 'gzip, deflate',
      if (isJson) 'Content-Type': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }

  Uri _buildUriForBase(
    String targetBaseUrl,
    String path, [
    Map<String, String>? queryParameters,
  ]) {
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('$targetBaseUrl$normalizedPath');
    if (queryParameters != null && queryParameters.isNotEmpty) {
      return uri.replace(queryParameters: queryParameters);
    }
    return uri;
  }

  /// Executes an HTTP operation against the primary baseUrl, and if a network/timeout
  /// error occurs, automatically probes fallback candidate hosts (`192.168.1.7`, `10.0.2.2`, `127.0.0.1`).
  Future<http.Response> _sendWithFailover(
    Future<http.Response> Function(String candidateBase, Duration timeout)
        sender, {
    Duration primaryTimeout = const Duration(seconds: 6),
    Duration fallbackTimeout = const Duration(seconds: 4),
  }) async {
    final candidates = _candidateBaseUrls();
    Object? lastError;

    for (var i = 0; i < candidates.length; i++) {
      final candidate = candidates[i];
      final timeout = i == 0 ? primaryTimeout : fallbackTimeout;
      try {
        final response = await sender(candidate, timeout);
        if (_cachedBaseUrl != candidate && _enableAutoFailover) {
          _cachedBaseUrl = candidate;
          unawaited(_storage.write(key: _serverUrlKey, value: candidate));
        }
        return response;
      } on SocketException catch (e) {
        lastError = e;
      } on TimeoutException catch (e) {
        lastError = e;
      } on http.ClientException catch (e) {
        lastError = e;
      }
    }

    if (lastError is TimeoutException) {
      throw lastError;
    }
    throw const SocketException('All candidate backend hosts unreachable');
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, String>? queryParameters,
  }) async {
    try {
      final headers = await _buildHeaders();
      final response = await _sendWithFailover(
        (candidateBase, timeout) => _httpClient
            .get(
              _buildUriForBase(candidateBase, path, queryParameters),
              headers: headers,
            )
            .timeout(timeout),
      );
      return _parseResponse(response);
    } on SocketException {
      throw ApiException(
        message:
            'Unable to connect to the server ($baseUrl). Make sure "php artisan serve" is running and your device is on the same network.',
        isOffline: true,
      );
    } on TimeoutException {
      throw ApiException(
        message:
            'Connection timed out ($baseUrl). Make sure "php artisan serve" is running.',
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
      final encodedBody = body != null ? jsonEncode(body) : null;
      final response = await _sendWithFailover(
        (candidateBase, timeout) => _httpClient
            .post(
              _buildUriForBase(candidateBase, path),
              headers: headers,
              body: encodedBody,
            )
            .timeout(timeout),
      );
      return _parseResponse(response);
    } on SocketException {
      throw ApiException(
        message:
            'Unable to connect to the server ($baseUrl). Make sure "php artisan serve" is running.',
        isOffline: true,
      );
    } on TimeoutException {
      throw ApiException(
        message:
            'Connection timed out ($baseUrl). Make sure "php artisan serve" is running.',
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
      final encodedBody = body != null ? jsonEncode(body) : null;
      final response = await _sendWithFailover(
        (candidateBase, timeout) => _httpClient
            .put(
              _buildUriForBase(candidateBase, path),
              headers: headers,
              body: encodedBody,
            )
            .timeout(timeout),
      );
      return _parseResponse(response);
    } on SocketException {
      throw ApiException(
        message:
            'Unable to connect to the server ($baseUrl). Make sure "php artisan serve" is running.',
        isOffline: true,
      );
    } on TimeoutException {
      throw ApiException(
        message:
            'Connection timed out ($baseUrl). Make sure "php artisan serve" is running.',
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
      final response = await _sendWithFailover(
        (candidateBase, timeout) async {
          final request = http.MultipartRequest(
            'POST',
            _buildUriForBase(candidateBase, path),
          );
          request.headers.addAll(headers);
          request.fields.addAll(fields);

          if (fileField != null && filePath != null && filePath.isNotEmpty) {
            request.files.add(
              await http.MultipartFile.fromPath(fileField, filePath),
            );
          }

          final streamedResponse = await request.send().timeout(timeout);
          return http.Response.fromStream(streamedResponse);
        },
        primaryTimeout: const Duration(seconds: 20),
        fallbackTimeout: const Duration(seconds: 12),
      );
      return _parseResponse(response);
    } on SocketException {
      throw const ApiException(
        message:
            'Cannot upload report while offline. Please verify "php artisan serve" is running and retry.',
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
