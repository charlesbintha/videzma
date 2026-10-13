import "dart:convert";
import "package:http/http.dart" as http;
import "../config/app_config.dart";
import "../models/auth_user.dart";
import "session_store.dart";

class ProfileService {
  ProfileService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Map<String, String> get _headers {
    final headers = <String, String>{
      "Content-Type": "application/json",
      "Accept": "application/json",
    };

    final token = SessionStore.token;
    if (token != null) {
      headers["Authorization"] = "Bearer $token";
    }

    return headers;
  }

  /// Recupere le profil de l'utilisateur connecte.
  Future<AuthUser?> getProfile() async {
    final response = await _client.get(
      Uri.parse("${AppConfig.apiBaseUrl}/api/auth/me"),
      headers: _headers,
    );

    if (response.statusCode < 200 || response.statusCode >= 300) {
      return null;
    }

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    final userData = payload["user"] as Map<String, dynamic>?;

    if (userData == null) return null;

    final user = AuthUser.fromJson(userData);
    await SessionStore.updateUser(user);
    return user;
  }

  /// Met a jour le profil de l'utilisateur.
  Future<ProfileUpdateResult> updateProfile({
    String? name,
    String? phone,
    String? locale,
  }) async {
    final body = <String, dynamic>{};
    if (name != null) body["name"] = name;
    if (phone != null) body["phone"] = phone;
    if (locale != null) body["locale"] = locale;

    final response = await _client.patch(
      Uri.parse("${AppConfig.apiBaseUrl}/api/auth/me"),
      headers: _headers,
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 || response.statusCode >= 300) {
      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final message = payload["message"] as String? ?? "Erreur inconnue";
      return ProfileUpdateResult(success: false, error: message);
    }

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    final userData = payload["user"] as Map<String, dynamic>?;

    if (userData == null) {
      return ProfileUpdateResult(
        success: false,
        error: "Reponse invalide du serveur",
      );
    }

    final user = AuthUser.fromJson(userData);
    await SessionStore.updateUser(user);
    return ProfileUpdateResult(success: true, user: user);
  }

  /// Deconnecte l'utilisateur.
  Future<bool> logout() async {
    final response = await _client.post(
      Uri.parse("${AppConfig.apiBaseUrl}/api/auth/logout"),
      headers: _headers,
    );

    // Clear session even if API call fails
    await SessionStore.clear();

    return response.statusCode >= 200 && response.statusCode < 300;
  }
}

class ProfileUpdateResult {
  final bool success;
  final AuthUser? user;
  final String? error;

  ProfileUpdateResult({
    required this.success,
    this.user,
    this.error,
  });
}
