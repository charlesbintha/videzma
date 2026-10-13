import "dart:convert";
import "../models/auth_user.dart";
import "api_client.dart";
import "session_store.dart";

class AuthResult {
  final bool ok;
  final String message;
  final AuthUser? user;

  AuthResult({
    required this.ok,
    required this.message,
    this.user,
  });
}

class AuthService {
  AuthService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  Future<AuthResult> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    final response = await _client.post("/api/auth/login", {
      "email": email,
      "password": password,
      "device_name": deviceName ?? "mobile",
    });

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 200 && response.statusCode < 300) {
      final token = payload["token"] as String?;
      final userJson = payload["user"] as Map<String, dynamic>?;
      if (token == null || userJson == null) {
        return AuthResult(ok: false, message: "Reponse invalide.");
      }
      final user = AuthUser.fromJson(userJson);
      // Sauvegarder la session de maniere persistante
      await SessionStore.saveSession(token, user);
      return AuthResult(ok: true, message: "Connexion OK.", user: user);
    }

    return AuthResult(
      ok: false,
      message: payload["message"] as String? ?? "Erreur de connexion.",
    );
  }

  Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    final response = await _client.post("/api/auth/register", {
      "name": name,
      "email": email,
      "phone": phone,
      "password": password,
    });

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 200 && response.statusCode < 300) {
      final token = payload["token"] as String?;
      final userJson = payload["user"] as Map<String, dynamic>?;
      if (token == null || userJson == null) {
        return AuthResult(ok: false, message: "Reponse invalide.");
      }
      final user = AuthUser.fromJson(userJson);
      // Sauvegarder la session de maniere persistante
      await SessionStore.saveSession(token, user);
      return AuthResult(ok: true, message: "Inscription OK.", user: user);
    }

    return AuthResult(
      ok: false,
      message: payload["message"] as String? ?? "Erreur inscription.",
    );
  }
}
