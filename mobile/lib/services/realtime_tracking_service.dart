import 'dart:async';
import '../models/driver_position.dart';
import 'websocket_service.dart';
import 'logger_service.dart';

/// Service for real-time driver tracking using WebSocket
class RealtimeTrackingService {
  static RealtimeTrackingService? _instance;

  final WebSocketService _ws;
  final _positionController = StreamController<DriverPosition>.broadcast();
  final _statusController = StreamController<Map<String, dynamic>>.broadcast();

  int? _currentServiceRequestId;
  bool _isTracking = false;

  RealtimeTrackingService._() : _ws = WebSocketService.instance;

  static RealtimeTrackingService get instance {
    _instance ??= RealtimeTrackingService._();
    return _instance!;
  }

  /// Stream of driver position updates
  Stream<DriverPosition> get positionStream => _positionController.stream;

  /// Stream of service request status changes
  Stream<Map<String, dynamic>> get statusStream => _statusController.stream;

  /// Whether currently tracking
  bool get isTracking => _isTracking;

  /// Current service request ID being tracked
  int? get currentServiceRequestId => _currentServiceRequestId;

  /// Start tracking a service request
  Future<void> startTracking(int serviceRequestId) async {
    if (_isTracking && _currentServiceRequestId == serviceRequestId) {
      logger.debug('RealtimeTracking', 'Already tracking service $serviceRequestId');
      return;
    }

    // Stop any existing tracking
    await stopTracking();

    _currentServiceRequestId = serviceRequestId;
    _isTracking = true;

    logger.info('RealtimeTracking', 'Starting tracking for service $serviceRequestId');

    // Connect to WebSocket if not connected
    if (!_ws.isConnected) {
      await _ws.connect();

      // Wait for connection to be established
      await _ws.connectionStatus.firstWhere((connected) => connected);
    }

    // Subscribe to the tracking channel
    final channelName = 'tracking.service.$serviceRequestId';
    await _ws.subscribePrivate(channelName);

    // Listen for position updates
    _ws.on(channelName, 'position.updated', _onPositionUpdated);

    // Listen for status changes
    _ws.on(channelName, 'status.changed', _onStatusChanged);

    logger.info('RealtimeTracking', 'Tracking started for service $serviceRequestId');
  }

  /// Stop tracking
  Future<void> stopTracking() async {
    if (!_isTracking || _currentServiceRequestId == null) {
      return;
    }

    final channelName = 'tracking.service.$_currentServiceRequestId';

    // Remove event listeners
    _ws.off(channelName, 'position.updated', _onPositionUpdated);
    _ws.off(channelName, 'status.changed', _onStatusChanged);

    // Unsubscribe from channel
    _ws.unsubscribe(channelName);

    logger.info('RealtimeTracking', 'Stopped tracking service $_currentServiceRequestId');

    _currentServiceRequestId = null;
    _isTracking = false;
  }

  void _onPositionUpdated(WebSocketEvent event) {
    try {
      final data = event.data;

      final position = DriverPosition(
        latitude: (data['lat'] as num).toDouble(),
        longitude: (data['lng'] as num).toDouble(),
        timestamp: data['timestamp'] as int,
        distanceMeters: data['distance'] as int?,
        etaMinutes: data['eta'] as int?,
        driverName: data['driver']?['name'] as String? ?? 'Vidangeur',
        driverPhone: data['driver']?['phone'] as String?,
      );

      _positionController.add(position);

      logger.debug(
        'RealtimeTracking',
        'Position updated: ${position.latitude}, ${position.longitude}',
      );
    } catch (e) {
      logger.error('RealtimeTracking', 'Error parsing position: $e');
    }
  }

  void _onStatusChanged(WebSocketEvent event) {
    try {
      final data = event.data;
      _statusController.add(data);

      logger.info('RealtimeTracking', 'Status changed: ${data['status']}');
    } catch (e) {
      logger.error('RealtimeTracking', 'Error parsing status: $e');
    }
  }

  /// Dispose resources
  void dispose() {
    stopTracking();
    _positionController.close();
    _statusController.close();
  }
}
