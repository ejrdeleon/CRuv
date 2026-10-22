import '../../../core/network/api_client.dart';
import '../domain/user_model.dart';

class AuthRepository {
  final ApiClient _apiClient;

  AuthRepository(this._apiClient);

  Future<bool> hasStoredSession() async {
    final token = await _apiClient.getToken();
    return token != null && token.isNotEmpty;
  }

  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final response = await _apiClient.post(
      '/login',
      body: {
        'email': email.trim(),
        'password': password,
        'device_name': 'flutter_mobile_app',
      },
    );

    final token = response['token'] as String;
    await _apiClient.saveToken(token);

    return UserModel.fromJson(response['user'] as Map<String, dynamic>);
  }

  Future<UserModel> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? barangay,
    String? address,
  }) async {
    final response = await _apiClient.post(
      '/register',
      body: {
        'name': name.trim(),
        'email': email.trim(),
        'password': password,
        'password_confirmation': passwordConfirmation,
        if (phone != null && phone.trim().isNotEmpty) 'phone': phone.trim(),
        if (barangay != null && barangay.trim().isNotEmpty)
          'barangay': barangay.trim(),
        if (address != null && address.trim().isNotEmpty)
          'address': address.trim(),
        'device_name': 'flutter_mobile_app',
      },
    );

    final token = response['token'] as String;
    await _apiClient.saveToken(token);

    return UserModel.fromJson(response['user'] as Map<String, dynamic>);
  }

  Future<({UserModel user, UserStats stats})> fetchProfile() async {
    final response = await _apiClient.get('/profile');
    final user = UserModel.fromJson(response['data'] as Map<String, dynamic>);
    final stats = UserStats.fromJson(response['stats'] as Map<String, dynamic>?);
    return (user: user, stats: stats);
  }

  Future<UserModel> updateProfile({
    required String name,
    required String email,
    String? phone,
    String? barangay,
    String? address,
    String? currentPassword,
    String? newPassword,
    String? newPasswordConfirmation,
  }) async {
    final body = <String, dynamic>{
      'name': name.trim(),
      'email': email.trim(),
      'phone': phone?.trim(),
      'barangay': barangay?.trim(),
      'address': address?.trim(),
    };

    if (newPassword != null && newPassword.isNotEmpty) {
      body['current_password'] = currentPassword;
      body['password'] = newPassword;
      body['password_confirmation'] = newPasswordConfirmation;
    }

    final response = await _apiClient.put('/profile', body: body);
    return UserModel.fromJson(response['data'] as Map<String, dynamic>);
  }

  Future<void> logout() async {
    try {
      await _apiClient.post('/logout');
    } catch (_) {
      // Clear local token even if the network request fails.
    } finally {
      await _apiClient.clearToken();
    }
  }
}
