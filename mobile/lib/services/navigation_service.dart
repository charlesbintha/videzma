import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import '../models/navigation_route.dart';
import 'api_client.dart';
import 'logger_service.dart';

class NavigationService {
  static const _tag = 'NavigationService';
  final ApiClient _client;
  final Stream<Position> Function()? _positionSource;
  NavigationService(
      {ApiClient? client, Stream<Position> Function()? positionSource})
      : _client = client ?? ApiClient(),
        _positionSource = positionSource;
  Position? currentPosition;
  int _generation = 0;
  bool _disposed = false;

  // Stream pour la position du vidangeur
  StreamSubscription<Position>? _positionSubscription;
  final StreamController<Position> _positionController =
      StreamController<Position>.broadcast();

  // Session de navigation en cours
  int? _currentSessionId;
  NavigationRoute? _currentRoute;

  Stream<Position> get positionStream => _positionController.stream;
  NavigationRoute? get currentRoute => _currentRoute;
  bool get isNavigating => _currentSessionId != null;

  /// Demarre la navigation vers un client
  Future<NavigationResult> startNavigation({
    required int clientId,
    int? serviceRequestId,
    double? originLat,
    double? originLng,
  }) async {
    final generation = ++_generation;
    logger.info(_tag, 'Demarrage navigation vers client #$clientId...');

    try {
      // Si pas de position fournie, obtenir la position actuelle
      if (originLat == null || originLng == null) {
        final position = await _getCurrentPosition();
        currentPosition = position;
        originLat = position.latitude;
        originLng = position.longitude;
      }

      if (_disposed || generation != _generation) {
        return NavigationResult(success: false, message: 'Navigation annulée');
      }
      // Utiliser la API de navigation vidangeur
      final response = await _client.post('/api/driver/navigation/start', {
        'service_request_id': serviceRequestId,
        'origin_latitude': originLat,
        'origin_longitude': originLng,
      }).timeout(const Duration(seconds: 25));

      if (response.statusCode != 200) {
        final body = jsonDecode(response.body) as Map<String, dynamic>;
        logger.error(_tag, 'Erreur: ${response.statusCode}');
        return NavigationResult(
          success: false,
          message: body['message'] as String? ??
              'Erreur lors du calcul de l\'itineraire',
        );
      }

      final json = jsonDecode(response.body) as Map<String, dynamic>;
      final route =
          NavigationRoute.fromJson(json['data'] as Map<String, dynamic>);

      if (_disposed || generation != _generation) {
        if (_disposed) {
          await _client.post('/api/driver/navigation/stop', {
            'service_request_id': route.sessionId,
          }).timeout(const Duration(seconds: 10));
        }
        return NavigationResult(success: false, message: 'Navigation annulée');
      }
      _currentSessionId = route.sessionId;
      _currentRoute = route;

      // Demarrer le suivi GPS
      await _startLocationTracking();

      logger.info(_tag, 'Navigation demarree - Session #${route.sessionId}');

      return NavigationResult(
        success: true,
        route: route,
      );
    } catch (e) {
      logger.error(_tag, 'Erreur demarrage navigation', e);
      return NavigationResult(
        success: false,
        message: 'Erreur: $e',
      );
    }
  }

  /// Met a jour l'itineraire avec la position actuelle
  Future<NavigationResult> updateRoute({
    required int clientId,
    int? serviceRequestId,
    Position? position,
  }) async {
    final generation = _generation;
    if (_currentSessionId == null) {
      return NavigationResult(
        success: false,
        message: 'Aucune navigation en cours',
      );
    }

    logger.info(_tag, 'Mise a jour itineraire...');

    try {
      position ??= await _getCurrentPosition();

      if (_disposed || generation != _generation) {
        return NavigationResult(success: false, message: 'Navigation annulée');
      }
      // Utiliser la API de navigation vidangeur
      final response = await _client.post('/api/driver/navigation/refresh', {
        'service_request_id': serviceRequestId ?? _currentSessionId,
        'latitude': position.latitude,
        'longitude': position.longitude,
        'captured_at': position.timestamp.toUtc().toIso8601String(),
      }).timeout(const Duration(seconds: 20));

      if (response.statusCode != 200) {
        logger.error(_tag, 'Erreur mise a jour: ${response.statusCode}');
        return NavigationResult(
          success: false,
          message: 'Erreur lors de la mise a jour',
        );
      }

      final json = jsonDecode(response.body) as Map<String, dynamic>;
      final route =
          NavigationRoute.fromJson(json['data'] as Map<String, dynamic>);

      if (_disposed || generation != _generation) {
        return NavigationResult(success: false, message: 'Navigation annulée');
      }
      _currentRoute = route;

      logger.info(_tag, 'Itineraire mis a jour - ETA: ${route.etaMinutes} min');

      return NavigationResult(
        success: true,
        route: route,
      );
    } catch (e) {
      logger.error(_tag, 'Erreur mise a jour itineraire', e);
      return NavigationResult(
        success: false,
        message: 'Erreur: $e',
      );
    }
  }

  /// Met a jour la position du vidangeur sur le serveur
  Future<PositionUpdateResult> updatePositionOnServer(Position position) async {
    final generation = _generation;
    if (_currentSessionId == null) {
      return PositionUpdateResult(
        success: false,
        message: 'Aucune navigation en cours',
      );
    }

    try {
      final response = await _client.post('/api/driver/navigation/position', {
        'service_request_id': _currentSessionId,
        'latitude': position.latitude,
        'longitude': position.longitude,
        'captured_at': position.timestamp.toUtc().toIso8601String(),
        'speed': position.speed < 0 ? 0 : position.speed,
        'heading': position.heading < 0 ? 0 : position.heading,
      }).timeout(const Duration(seconds: 10));

      if (response.statusCode != 200) {
        logger.warning(
            _tag, 'Erreur mise a jour position: ${response.statusCode}');
        if (!_disposed &&
            generation == _generation &&
            [403, 404, 409].contains(response.statusCode)) {
          ++_generation;
          _currentSessionId = null;
          _currentRoute = null;
          await _stopLocationTracking();
        }
        return PositionUpdateResult(
          success: false,
          message: 'Erreur mise a jour position',
        );
      }

      final json = jsonDecode(response.body) as Map<String, dynamic>;
      final data = json['data'] as Map<String, dynamic>?;

      return PositionUpdateResult(
        success: true,
        distanceMeters: (data?['distance_to_client'] as num?)?.toDouble(),
        etaMinutes: (data?['eta_minutes'] as num?)?.toInt(),
        formattedDistance: data?['formatted_distance'] as String?,
        formattedEta: data?['formatted_eta'] as String?,
      );
    } catch (e) {
      logger.warning(_tag, 'Erreur mise a jour position serveur: $e');
      return PositionUpdateResult(
        success: false,
        message: 'Erreur: $e',
      );
    }
  }

  /// Arrete la navigation
  Future<void> stopNavigation() async {
    logger.info(_tag, 'Arret navigation...');

    ++_generation;
    final sessionId = _currentSessionId;
    _currentSessionId = null;
    _currentRoute = null;
    await _stopLocationTracking();

    // Notifier le serveur après avoir arrêté la capture locale.
    if (sessionId != null) {
      try {
        await _client.post('/api/driver/navigation/stop', {
          'service_request_id': sessionId,
        }).timeout(const Duration(seconds: 10));
      } catch (e) {
        logger.warning(_tag, 'Erreur notification arret: $e');
      }
    }

    logger.info(_tag, 'Navigation arretee');
  }

  /// Obtient la position actuelle
  Future<Position> _getCurrentPosition() async {
    logger.debug(_tag, 'Obtention position actuelle...');

    if (!await Geolocator.isLocationServiceEnabled()) {
      throw Exception('Activez le GPS du téléphone');
    }
    // Verifier les permissions
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        throw Exception('Permission de localisation refusee');
      }
    }

    if (permission == LocationPermission.deniedForever) {
      throw Exception('Permission de localisation refusee definitivement');
    }

    // Obtenir la position
    final position = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.bestForNavigation,
        timeLimit: Duration(seconds: 20),
      ),
    );

    logger.debug(_tag, 'Position: ${position.latitude}, ${position.longitude}');
    return position;
  }

  /// Demarre le suivi de position en temps reel
  Future<void> _startLocationTracking() async {
    logger.info(_tag, 'Demarrage suivi GPS...');

    await _stopLocationTracking();
    if (_disposed || _currentSessionId == null) return;

    final LocationSettings locationSettings = !kIsWeb &&
            defaultTargetPlatform == TargetPlatform.android
        ? AndroidSettings(
            accuracy: LocationAccuracy.bestForNavigation,
            distanceFilter: 0,
            intervalDuration: const Duration(seconds: 2),
            foregroundNotificationConfig: const ForegroundNotificationConfig(
              notificationTitle: 'Navigation Videzma',
              notificationText: 'Suivi du trajet vers le client en cours',
              enableWakeLock: true,
            ),
          )
        : const LocationSettings(
            accuracy: LocationAccuracy.bestForNavigation, distanceFilter: 3);

    _positionSubscription = (_positionSource?.call() ??
            Geolocator.getPositionStream(
              locationSettings: locationSettings,
            ))
        .listen(
      (position) {
        if (_disposed) return;
        currentPosition = position;
        _positionController.add(position);
      },
      onError: (e) {
        logger.error(_tag, 'Erreur GPS', e);
        if (!_disposed) _positionController.addError(e);
      },
    );

    logger.info(_tag, 'Suivi GPS demarre');
  }

  /// Arrete le suivi de position
  Future<void> _stopLocationTracking() async {
    final subscription = _positionSubscription;
    _positionSubscription = null;
    await subscription?.cancel();
    logger.info(_tag, 'Suivi GPS arrete');
  }

  /// Calcule la distance entre deux points
  double calculateDistance(
    double lat1,
    double lng1,
    double lat2,
    double lng2,
  ) {
    return Geolocator.distanceBetween(lat1, lng1, lat2, lng2);
  }

  /// Libere les ressources
  void dispose() {
    _disposed = true;
    ++_generation;
    _stopLocationTracking();
    _positionController.close();
  }
}

class NavigationResult {
  final bool success;
  final String? message;
  final NavigationRoute? route;

  NavigationResult({
    required this.success,
    this.message,
    this.route,
  });
}

class PositionUpdateResult {
  final bool success;
  final String? message;
  final double? distanceMeters;
  final int? etaMinutes;
  final String? formattedDistance;
  final String? formattedEta;

  PositionUpdateResult({
    required this.success,
    this.message,
    this.distanceMeters,
    this.etaMinutes,
    this.formattedDistance,
    this.formattedEta,
  });
}
