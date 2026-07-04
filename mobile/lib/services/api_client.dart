import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Thrown for any non-2xx API response. [errors] holds Laravel's per-field
/// validation messages (422 responses), keyed by field name.
class ApiException implements Exception {
  final int statusCode;
  final String message;
  final Map<String, List<String>> errors;

  ApiException(this.statusCode, this.message, {this.errors = const {}});

  String? errorFor(String field) => errors[field]?.first;

  @override
  String toString() => message;
}

/// Thin wrapper around the DjidjiMarket REST API (see spec section 5).
///
/// Base URL: on a real device/emulator this must point at the deployed API,
/// not localhost. Override via `--dart-define=API_BASE_URL=...` at build
/// time. The default assumes `php artisan serve` on the same machine
/// (works for the web/desktop targets used during development, since no
/// Android/iOS toolchain is set up yet — see spec section 11).
class ApiClient {
  static const _defaultBaseUrl = 'http://127.0.0.1:8123/api';
  static const _tokenKey = 'auth_token';

  final String baseUrl;
  final _storage = const FlutterSecureStorage();

  ApiClient({String? baseUrl})
      : baseUrl = baseUrl ??
            const String.fromEnvironment('API_BASE_URL', defaultValue: _defaultBaseUrl);

  Future<String?> get token => _storage.read(key: _tokenKey);

  Future<void> setToken(String token) => _storage.write(key: _tokenKey, value: token);

  Future<void> clearToken() => _storage.delete(key: _tokenKey);

  Future<Map<String, String>> _headers({bool auth = true}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (auth) {
      final token = await this.token;
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  Future<dynamic> get(String path, {bool auth = true}) async {
    final response = await http.get(Uri.parse('$baseUrl$path'), headers: await _headers(auth: auth));

    return _handle(response);
  }

  Future<dynamic> post(String path, {Map<String, dynamic>? body, bool auth = true}) async {
    final response = await http.post(
      Uri.parse('$baseUrl$path'),
      headers: await _headers(auth: auth),
      body: jsonEncode(body ?? {}),
    );

    return _handle(response);
  }

  Future<dynamic> put(String path, {Map<String, dynamic>? body, bool auth = true}) async {
    final response = await http.put(
      Uri.parse('$baseUrl$path'),
      headers: await _headers(auth: auth),
      body: jsonEncode(body ?? {}),
    );

    return _handle(response);
  }

  Future<dynamic> patch(String path, {Map<String, dynamic>? body, bool auth = true}) async {
    final response = await http.patch(
      Uri.parse('$baseUrl$path'),
      headers: await _headers(auth: auth),
      body: jsonEncode(body ?? {}),
    );

    return _handle(response);
  }

  Future<dynamic> delete(String path, {bool auth = true}) async {
    final response = await http.delete(Uri.parse('$baseUrl$path'), headers: await _headers(auth: auth));

    return _handle(response);
  }

  dynamic _handle(http.Response response) {
    final decoded = response.body.isNotEmpty ? jsonDecode(response.body) : null;

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    final message = decoded is Map && decoded['message'] != null
        ? decoded['message'] as String
        : 'Une erreur est survenue (${response.statusCode}).';

    final rawErrors = decoded is Map ? decoded['errors'] : null;
    final errors = <String, List<String>>{};
    if (rawErrors is Map) {
      rawErrors.forEach((key, value) {
        errors[key] = (value as List).map((e) => e.toString()).toList();
      });
    }

    throw ApiException(response.statusCode, message, errors: errors);
  }
}
