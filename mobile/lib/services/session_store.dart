import "dart:convert";
import "package:shared_preferences/shared_preferences.dart";
import "../models/auth_user.dart";
import "logger_service.dart";

class SessionStore {
  static const _tag = 'SessionStore';
  static const _tokenKey = 'auth_token';
  static const _userKey = 'auth_user';
  static const _locationPermissionKey = 'location_permission_granted';

  static String? _token;
  static AuthUser? _user;
  static bool _locationPermissionGranted = false;

  static String? get token => _token;
  static AuthUser? get user => _user;
  static bool get locationPermissionGranted => _locationPermissionGranted;

  static bool get isLoggedIn => _token != null && _user != null;

  /// Initialise le store au demarrage de l'app
  static Future<void> init() async {
    logger.info(_tag, 'Initialisation du SessionStore...');
    final prefs = await SharedPreferences.getInstance();

    _token = prefs.getString(_tokenKey);
    _locationPermissionGranted = prefs.getBool(_locationPermissionKey) ?? false;

    final userJson = prefs.getString(_userKey);
    if (userJson != null) {
      try {
        final userData = jsonDecode(userJson) as Map<String, dynamic>;
        _user = AuthUser.fromJson(userData);
        logger.info(_tag, 'Session restauree pour: ${_user?.email}');
      } catch (e) {
        logger.error(_tag, 'Erreur parsing user', e);
        _user = null;
      }
    }

    logger.info(_tag, 'Token present: ${_token != null}, User present: ${_user != null}');
  }

  /// Sauvegarde le token et l'utilisateur
  static Future<void> saveSession(String token, AuthUser user) async {
    logger.info(_tag, 'Sauvegarde session pour: ${user.email}');
    final prefs = await SharedPreferences.getInstance();

    _token = token;
    _user = user;

    await prefs.setString(_tokenKey, token);
    await prefs.setString(_userKey, jsonEncode(user.toJson()));

    logger.info(_tag, 'Session sauvegardee');
  }

  /// Met a jour l'utilisateur
  static Future<void> updateUser(AuthUser user) async {
    logger.info(_tag, 'Mise a jour utilisateur: ${user.email}');
    final prefs = await SharedPreferences.getInstance();

    _user = user;
    await prefs.setString(_userKey, jsonEncode(user.toJson()));
  }

  /// Sauvegarde la permission de localisation
  static Future<void> setLocationPermissionGranted(bool granted) async {
    logger.info(_tag, 'Permission localisation: $granted');
    final prefs = await SharedPreferences.getInstance();

    _locationPermissionGranted = granted;
    await prefs.setBool(_locationPermissionKey, granted);
  }

  /// Efface la session (deconnexion)
  static Future<void> clear() async {
    logger.info(_tag, 'Effacement de la session');
    final prefs = await SharedPreferences.getInstance();

    _token = null;
    _user = null;

    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
    // On garde la permission de localisation

    logger.info(_tag, 'Session effacee');
  }
}
