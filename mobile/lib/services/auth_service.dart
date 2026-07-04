import 'package:flutter/foundation.dart';
import '../models/user.dart';
import 'api_client.dart';

class AuthService extends ChangeNotifier {
  final ApiClient _client;

  AppUser? currentUser;
  bool isLoading = true;

  AuthService(this._client) {
    _restoreSession();
  }

  bool get isAuthenticated => currentUser != null;

  Future<void> _restoreSession() async {
    final token = await _client.token;

    if (token != null) {
      try {
        final json = await _client.get('/user');
        currentUser = AppUser.fromJson(json);
      } catch (_) {
        await _client.clearToken();
      }
    }

    isLoading = false;
    notifyListeners();
  }

  /// On success the phone either just registered or already had a pending
  /// signup — either way an OTP has been sent and the caller should move to
  /// the verification screen.
  Future<void> register({
    required String name,
    required String phone,
    required String password,
    required String role,
  }) async {
    await _client.post('/auth/register', body: {
      'name': name,
      'phone': phone,
      'password': password,
      'password_confirmation': password,
      'role': role,
    }, auth: false);
  }

  Future<void> verifyOtp({required String phone, required String code}) async {
    final json = await _client.post('/auth/otp/verify', body: {
      'phone': phone,
      'code': code,
    }, auth: false);

    await _client.setToken(json['token']);
    currentUser = AppUser.fromJson(json['user']);
    notifyListeners();
  }

  Future<void> login({required String phone, required String password}) async {
    final json = await _client.post('/auth/login', body: {
      'phone': phone,
      'password': password,
    }, auth: false);

    await _client.setToken(json['token']);
    currentUser = AppUser.fromJson(json['user']);
    notifyListeners();
  }

  Future<void> logout() async {
    await _client.clearToken();
    currentUser = null;
    notifyListeners();
  }
}
