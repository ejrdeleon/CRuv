import 'package:flutter/foundation.dart';
import '../../../core/network/api_client.dart';
import '../data/auth_repository.dart';
import '../domain/user_model.dart';

class AuthProvider extends ChangeNotifier {
  final AuthRepository _repository;

  UserModel? _user;
  UserStats _stats = const UserStats();
  bool _isInitializing = true;
  bool _isLoading = false;
  String? _errorMessage;

  AuthProvider(this._repository);

  UserModel? get user => _user;
  UserStats get stats => _stats;
  bool get isInitializing => _isInitializing;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _user != null;
  String? get errorMessage => _errorMessage;

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  /// Checks secure storage on app launch and restores the user session if valid.
  Future<void> initializeSession() async {
    _isInitializing = true;
    notifyListeners();

    try {
      final hasToken = await _repository.hasStoredSession();
      if (hasToken) {
        final profile = await _repository.fetchProfile();
        _user = profile.user;
        _stats = profile.stats;
      }
    } on ApiException catch (e) {
      if (e.statusCode == 401) {
        await _repository.logout();
        _user = null;
      }
    } catch (_) {
      // Keep initialized state false so the user can log in cleanly.
    } finally {
      _isInitializing = false;
      notifyListeners();
    }
  }

  Future<bool> login({
    required String email,
    required String password,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _user = await _repository.login(email: email, password: password);
      await refreshProfile();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (_) {
      _errorMessage = 'Unable to sign in right now. Please try again.';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? barangay,
    String? address,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _user = await _repository.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        phone: phone,
        barangay: barangay,
        address: address,
      );
      await refreshProfile();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (_) {
      _errorMessage = 'Registration failed. Please check your details.';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshProfile() async {
    try {
      final profile = await _repository.fetchProfile();
      _user = profile.user;
      _stats = profile.stats;
      notifyListeners();
    } catch (_) {
      // Silently ignore background profile refresh errors.
    }
  }

  Future<bool> updateProfile({
    required String name,
    required String email,
    String? phone,
    String? barangay,
    String? address,
    String? currentPassword,
    String? newPassword,
    String? newPasswordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _user = await _repository.updateProfile(
        name: name,
        email: email,
        phone: phone,
        barangay: barangay,
        address: address,
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (_) {
      _errorMessage = 'Failed to update profile. Please try again.';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();
    await _repository.logout();
    _user = null;
    _stats = const UserStats();
    _isLoading = false;
    notifyListeners();
  }
}
