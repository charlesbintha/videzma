import "dart:async";
import "dart:convert";
import "package:geolocator/geolocator.dart";
import "../services/api_client.dart";
import "location_store.dart";
import "logger_service.dart";

class LocationService {
  static const _tag = 'LocationService';

  LocationService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;
  String? lastError;

  Future<bool> captureAndSend() async {
    logger.info(_tag, 'Debut capture position...');
    lastError = null;
    final position = await _determinePosition().timeout(
      const Duration(seconds: 20),
      onTimeout: () {
        lastError =
            'Aucun signal GPS reçu. Vérifiez la localisation du navigateur et de l’appareil.';
        return null;
      },
    );

    if (position == null) {
      logger.warning(_tag, 'Position null - capture echouee');
      return false;
    }

    logger.info(_tag,
        'Position obtenue: lat=${position.latitude}, lng=${position.longitude}');
    LocationStore.set(position.latitude, position.longitude);

    return sendPosition(
      latitude: position.latitude,
      longitude: position.longitude,
      capturedAt: position.timestamp,
    );
  }

  Future<Position?> _determinePosition() async {
    try {
      logger.debug(_tag, 'Verification service localisation...');
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        lastError = 'Le service de localisation de l’appareil est désactivé.';
        logger.warning(_tag, 'Service localisation desactive');
        return null;
      }
      logger.debug(_tag, 'Service localisation OK');

      var permission = await Geolocator.checkPermission();
      logger.debug(_tag, 'Permission actuelle: $permission');

      if (permission == LocationPermission.denied) {
        logger.info(_tag, 'Demande de permission...');
        permission = await Geolocator.requestPermission();
        logger.info(_tag, 'Nouvelle permission: $permission');
      }

      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        lastError =
            'Accès à la localisation refusé. Autorisez-le dans les réglages du navigateur.';
        logger.warning(_tag, 'Permission refusee: $permission');
        return null;
      }

      logger.debug(_tag, 'Recuperation position GPS...');
      return await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
            accuracy: LocationAccuracy.high, timeLimit: Duration(seconds: 15)),
      );
    } catch (e) {
      lastError =
          'Position GPS indisponible. Vérifiez les autorisations et réessayez.';
      logger.error(_tag, 'Erreur _determinePosition', e);
      return null;
    }
  }

  Future<Stream<Position>?> positionStream() async {
    lastError = null;
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        lastError = 'Le service de localisation est désactivé.';
        return null;
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        lastError =
            'Accès à la localisation refusé. Vérifiez les réglages du navigateur.';
        return null;
      }

      return Geolocator.getPositionStream(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 10,
        ),
      );
    } catch (_) {
      lastError = 'La localisation n’est pas disponible dans ce navigateur.';
      return null;
    }
  }

  Future<bool> sendPosition({
    required double latitude,
    required double longitude,
    DateTime? capturedAt,
  }) async {
    lastError = null;
    try {
      logger.info(
          _tag, 'Envoi position au serveur: lat=$latitude, lng=$longitude');
      final response = await _client.post("/api/locations", {
        "latitude": latitude,
        "longitude": longitude,
        if (capturedAt != null)
          "captured_at": capturedAt.toUtc().toIso8601String(),
      }).timeout(const Duration(seconds: 10));

      logger.debug(_tag, 'Reponse serveur: ${response.statusCode}');

      if (response.statusCode < 200 || response.statusCode >= 300) {
        lastError = 'Position non enregistrée. Réessayez ou reconnectez-vous.';
        logger.warning(
            _tag, 'Erreur serveur: ${response.statusCode} - ${response.body}');
        return false;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload["data"] as Map<String, dynamic>?;
      final id = data?["id"] as int?;
      if (id == null) {
        lastError = 'Le serveur n’a pas confirmé l’enregistrement.';
        return false;
      }
      LocationStore.setLocationId(id);
      logger.info(_tag, 'Position enregistree avec succes, ID: $id');

      return true;
    } catch (e) {
      lastError = 'Envoi impossible. Vérifiez la connexion au serveur.';
      logger.error(_tag, 'Erreur sendPosition', e);
      return false;
    }
  }
}
