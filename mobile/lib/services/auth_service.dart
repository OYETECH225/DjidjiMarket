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

  /// Re-fetches the current user — needed after an action that can change
  /// the account server-side without going through [updateProfile], e.g.
  /// "Devenir vendeur" promoting a client to role=vendor.
  Future<void> refreshUser() async {
    final json = await _client.get('/user');
    currentUser = AppUser.fromJson(json);
    notifyListeners();
  }

  /// Sends an OTP to [phone] whether it's already registered or not, and
  /// returns whether the phone is new — the caller must collect a name
  /// before calling [verifyOtp] if so, since there's no password/registration
  /// step separate from OTP verification.
  Future<bool> requestOtp(String phone) async {
    final json = await _client.post('/auth/otp/request', body: {'phone': phone}, auth: false);

    return json['is_new'] as bool;
  }

  /// Verifies the OTP and logs the user in. [name]/[role] are required only
  /// when the phone is new (see [requestOtp]) — the backend ignores them for
  /// an existing phone.
  Future<void> verifyOtp({
    required String phone,
    required String code,
    String? name,
    String? role,
  }) async {
    final json = await _client.post('/auth/otp/verify', body: {
      'phone': phone,
      'code': code,
      if (name != null) 'name': name,
      if (role != null) 'role': role,
    }, auth: false);

    await _client.setToken(json['token']);
    currentUser = AppUser.fromJson(json['user']);
    notifyListeners();
  }

  Future<void> updateProfile({required String name, String? email}) async {
    final json = await _client.patch('/user', body: {
      'name': name,
      if (email != null) 'email': email,
    });

    currentUser = AppUser.fromJson(json['data']);
    notifyListeners();
  }

  Future<void> logout() async {
    await _client.clearToken();
    currentUser = null;
    notifyListeners();
  }
}
