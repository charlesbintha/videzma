import 'dart:async';
import 'dart:convert';
import 'dart:math';
import '../models/driver_position.dart';
import 'api_client.dart';
import 'logger_service.dart';

/// Service for real-time driver tracking using adaptive polling
///
/// Features:
/// - Adaptive polling interval (3s when moving, up to 10s when stationary)
/// - ETag support to reduce bandwidth when position unchanged
/// - Automatic reconnection on network errors
class DriverTrackingService {
  static const _tag = 'DriverTrackingService';

  final ApiClient _client;
  Timer? _pollingTimer;
  final StreamController<DriverPosition?> _positionController =
      StreamController<DriverPosition?>.broadcast();

  String? _lastEtag;
  int _pollInterval = 3000; // Start at 3 seconds
  int _unchangedCount = 0;
  int? _currentServiceRequestId;
  bool _isTracking = false;
  int _generation = 0;

  // Configuration
  static const int _minPollInterval = 3000; // 3 seconds minimum
  static const int _maxPollInterval = 10000; // 10 seconds maximum
  static const int _unchangedThreshold =
      3; // Increase interval after 3 unchanged responses

  DriverTrackingService({ApiClient? client}) : _client = client ?? ApiClient();

  /// Stream of driver positions - subscribe to receive updates
  Stream<DriverPosition?> get positionStream => _positionController.stream;

  /// Whether tracking is currently active
  bool get isTracking => _isTracking;

  /// Current polling interval in milliseconds
  int get currentPollInterval => _pollInterval;

  /// Start tracking a driver for a specific service request
  void startTracking(int serviceRequestId) {
    logger.info(
        _tag, 'Starting tracking for service request #$serviceRequestId');

    // Stop any existing tracking
    stopTracking();

    _currentServiceRequestId = serviceRequestId;
    _lastEtag = null;
    _unchangedCount = 0;
    _pollInterval = _minPollInterval;
    _isTracking = true;

    // Start polling immediately
    _poll();
  }

  /// Stop tracking
  void stopTracking() {
    logger.info(_tag, 'Stopping tracking');

    _generation++;
    _pollingTimer?.cancel();
    _pollingTimer = null;
    _isTracking = false;
    _currentServiceRequestId = null;
    _lastEtag = null;
    _unchangedCount = 0;
    _pollInterval = _minPollInterval;
  }

  /// Internal polling method
  Future<void> _poll() async {
    if (!_isTracking || _currentServiceRequestId == null) {
      return;
    }

    final generation = ++_generation;
    try {
      final response = await _client.getWithEtag(
        '/api/tracking/$_currentServiceRequestId',
        etag: _lastEtag,
      );

      if (!_isTracking || generation != _generation) return;

      if (response.statusCode == 304) {
        // Position unchanged - gradually increase polling interval
        _unchangedCount++;
        if (_unchangedCount > _unchangedThreshold) {
          _pollInterval = min(_maxPollInterval, _pollInterval + 1000);
          logger.debug(
              _tag, 'Position unchanged, slowing poll to ${_pollInterval}ms');
        }
      } else if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;

        // Update ETag for next request
        _lastEtag = response.headers['etag'];

        // Reset to fast polling when position changes
        if (_unchangedCount > 0) {
          _unchangedCount = 0;
          _pollInterval = _minPollInterval;
          logger.debug(
              _tag, 'Position changed, resetting poll to ${_pollInterval}ms');
        }

        // Parse and emit position
        if (data['tracking'] != true) {
          _positionController.add(null);
          stopTracking();
          return;
        }
        if (data['tracking'] == true && data['p'] != null) {
          final position = DriverPosition.fromJson(data);
          logger.debug(_tag,
              'Driver position: ${position.formattedDistance}, ETA: ${position.formattedEta}');
          _positionController.add(position);
        } else {
          // Tracking active but no position yet
          logger.debug(_tag, 'Tracking active but no position available');
          _positionController.add(null);
        }
      } else if (response.statusCode == 404) {
        // Service request not found or tracking not active
        logger.warning(_tag, 'Tracking not available (404)');
        _positionController.add(null);
      } else {
        // Other error
        logger.warning(_tag, 'Unexpected status: ${response.statusCode}');
      }
    } catch (e) {
      if (!_isTracking || generation != _generation) return;
      _positionController.addError(Exception("Connexion interrompue"));
      // Network error - slow down polling significantly
      _pollInterval = min(_maxPollInterval, _pollInterval + 2000);
      logger.warning(
          _tag, 'Network error, slowing poll to ${_pollInterval}ms: $e');
    }

    // Schedule next poll
    if (_isTracking && generation == _generation) {
      _pollingTimer = Timer(
        Duration(milliseconds: _pollInterval),
        _poll,
      );
    }
  }

  /// Check if tracking is available for a service request
  Future<TrackingStatus> checkTrackingStatus(int serviceRequestId) async {
    try {
      final response =
          await _client.get('/api/tracking/$serviceRequestId/status');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return TrackingStatus.fromJson(data);
      } else if (response.statusCode == 404) {
        return TrackingStatus(available: false, reason: 'not_found');
      } else {
        return TrackingStatus(available: false, reason: 'error');
      }
    } catch (e) {
      logger.error(_tag, 'Error checking tracking status', e);
      return TrackingStatus(available: false, reason: 'network_error');
    }
  }

  /// Force an immediate poll (useful after user action)
  void forceRefresh() {
    if (_isTracking) {
      _pollingTimer?.cancel();
      _lastEtag = null; // Clear ETag to get full response
      _poll();
    }
  }

  /// Dispose resources
  void dispose() {
    stopTracking();
    _positionController.close();
  }
}
