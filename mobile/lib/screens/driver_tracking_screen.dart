import 'dart:async';
import 'package:flutter/material.dart';
import '../widgets/app_map.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/driver_position.dart';
import '../services/driver_tracking_service.dart';
import '../services/realtime_tracking_service.dart';
import '../services/logger_service.dart';
import '../theme/app_colors.dart';

/// Screen for clients to track driver location in real-time
class DriverTrackingScreen extends StatefulWidget {
  final int serviceRequestId;
  final double clientLatitude;
  final double clientLongitude;
  final String? clientAddress;

  const DriverTrackingScreen({
    super.key,
    required this.serviceRequestId,
    required this.clientLatitude,
    required this.clientLongitude,
    this.clientAddress,
  });

  @override
  State<DriverTrackingScreen> createState() => _DriverTrackingScreenState();
}

class _DriverTrackingScreenState extends State<DriverTrackingScreen>
    with TickerProviderStateMixin {
  AppMapController? _mapController;

  // Dual tracking: WebSocket (primary) + Polling (fallback)
  final RealtimeTrackingService _realtimeService =
      RealtimeTrackingService.instance;
  final DriverTrackingService _pollingService = DriverTrackingService();

  StreamSubscription<DriverPosition>? _wsPositionSubscription;
  StreamSubscription<DriverPosition?>? _pollingSubscription;

  bool _useWebSocket =
      false; // Polling also checks whether sharing has stopped.

  DriverPosition? _currentPosition;
  LatLng? _animatedDriverPosition;
  AnimationController? _markerAnimationController;
  Animation<double>? _latAnimation;
  Animation<double>? _lngAnimation;

  Timer? _freshnessTimer;
  bool _isLoading = true;
  bool _hasError = false;
  String? _errorMessage;
  String _connectionMode = 'Connexion...';

  @override
  void initState() {
    super.initState();
    _startTracking();
    _freshnessTimer = Timer.periodic(const Duration(seconds: 15), (_) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _freshnessTimer?.cancel();
    _wsPositionSubscription?.cancel();
    _pollingSubscription?.cancel();
    _realtimeService.stopTracking();
    _pollingService.dispose();
    _markerAnimationController?.dispose();
    super.dispose();
  }

  void _startTracking() {
    if (_useWebSocket) {
      _startWebSocketTracking();
    } else {
      _startPollingTracking();
    }
  }

  /// Start WebSocket real-time tracking
  Future<void> _startWebSocketTracking() async {
    logger.info('DriverTracking', 'Starting WebSocket tracking');

    try {
      await _realtimeService.startTracking(widget.serviceRequestId);

      _wsPositionSubscription = _realtimeService.positionStream.listen(
        (position) {
          if (!mounted) return;

          setState(() {
            _isLoading = false;
            _hasError = false;
            _connectionMode = 'Temps reel';
            _animateMarkerTo(position);
            _currentPosition = position;
          });
        },
        onError: (error) {
          logger.error('DriverTracking', 'WebSocket error: $error');
          _fallbackToPolling();
        },
      );

      // Set timeout - if no position received in 10s, fallback to polling
      Future.delayed(const Duration(seconds: 10), () {
        if (_isLoading && mounted && _useWebSocket) {
          logger.warning(
              'DriverTracking', 'WebSocket timeout, falling back to polling');
          _fallbackToPolling();
        }
      });
    } catch (e) {
      logger.error('DriverTracking', 'Failed to start WebSocket: $e');
      _fallbackToPolling();
    }
  }

  /// Fallback to HTTP polling
  void _fallbackToPolling() {
    if (!mounted) return;

    logger.info('DriverTracking', 'Falling back to polling');

    _useWebSocket = false;
    _wsPositionSubscription?.cancel();
    _realtimeService.stopTracking();

    setState(() {
      _connectionMode = 'Mode standard';
    });

    _startPollingTracking();
  }

  /// Start HTTP polling tracking
  void _startPollingTracking() {
    _connectionMode = 'Suivi actualisé';
    _pollingService.startTracking(widget.serviceRequestId);

    _pollingSubscription = _pollingService.positionStream.listen(
      (position) {
        if (!mounted) return;

        setState(() {
          _isLoading = false;
          _hasError = false;

          if (position != null) {
            _animateMarkerTo(position);
            _currentPosition = position;
          } else {
            _currentPosition = null;
            _animatedDriverPosition = null;
            _markerAnimationController?.stop();
          }
        });
      },
      onError: (error) {
        if (!mounted) return;
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = 'Erreur de connexion';
        });
      },
    );
  }

  void _forceRefresh() {
    if (_useWebSocket) {
      // WebSocket auto-updates, just restart tracking
      _realtimeService.stopTracking();
      _startWebSocketTracking();
    } else {
      _pollingService.forceRefresh();
    }
  }

  void _animateMarkerTo(DriverPosition newPosition) {
    final startLat = _animatedDriverPosition?.latitude ?? newPosition.latitude;
    final startLng =
        _animatedDriverPosition?.longitude ?? newPosition.longitude;

    _markerAnimationController?.dispose();
    _markerAnimationController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );

    _latAnimation = Tween<double>(
      begin: startLat,
      end: newPosition.latitude,
    ).animate(CurvedAnimation(
      parent: _markerAnimationController!,
      curve: Curves.easeInOut,
    ));

    _lngAnimation = Tween<double>(
      begin: startLng,
      end: newPosition.longitude,
    ).animate(CurvedAnimation(
      parent: _markerAnimationController!,
      curve: Curves.easeInOut,
    ));

    _markerAnimationController!.addListener(() {
      if (mounted) {
        setState(() {
          _animatedDriverPosition = LatLng(
            _latAnimation!.value,
            _lngAnimation!.value,
          );
        });
      }
    });

    _markerAnimationController!.forward();
  }

  void _fitBothMarkers() {
    if (_animatedDriverPosition == null || _mapController == null) return;

    final clientPos = LatLng(widget.clientLatitude, widget.clientLongitude);
    final driverPos = _animatedDriverPosition!;

    final bounds = MapBounds(
      southwest: LatLng(
        driverPos.latitude < clientPos.latitude
            ? driverPos.latitude
            : clientPos.latitude,
        driverPos.longitude < clientPos.longitude
            ? driverPos.longitude
            : clientPos.longitude,
      ),
      northeast: LatLng(
        driverPos.latitude > clientPos.latitude
            ? driverPos.latitude
            : clientPos.latitude,
        driverPos.longitude > clientPos.longitude
            ? driverPos.longitude
            : clientPos.longitude,
      ),
    );

    _mapController!.animateCamera(
      MapCameraUpdate.newLatLngBounds(bounds, 80),
    );
  }

  Future<void> _callDriver() async {
    final phone = _currentPosition?.driverPhone;
    if (phone == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Numero de telephone non disponible')),
      );
      return;
    }

    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  @override
  Widget build(BuildContext context) {
    final clientPosition =
        LatLng(widget.clientLatitude, widget.clientLongitude);

    // Build markers
    final markers = <MapMarker>{
      // Client destination marker
      MapMarker(
        id: 'client',
        position: clientPosition,
        color: Colors.green,
        title: 'Destination',
        snippet: widget.clientAddress ?? 'Votre position',
      ),
    };

    // Add driver marker if we have a position
    if (_animatedDriverPosition != null) {
      markers.add(
        MapMarker(
          id: 'driver',
          position: _animatedDriverPosition!,
          color: Colors.orange,
          title: _currentPosition?.driverName ?? 'Vidangeur',
          snippet: _currentPosition?.formattedEta ?? '',
        ),
      );
    }

    return Scaffold(
      body: Stack(
        children: [
          // Map
          AppMap(
            initialCameraPosition: MapCameraPosition(
              target: clientPosition,
              zoom: 14,
            ),
            markers: markers,
            onMapCreated: (controller) {
              _mapController = controller;
              // Fit both markers after a short delay
              Future.delayed(const Duration(milliseconds: 500), () {
                if (mounted && _animatedDriverPosition != null) {
                  _fitBothMarkers();
                }
              });
            },
            zoomControlsEnabled: false,
          ),

          // Top bar
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              child: Container(
                margin: const EdgeInsets.all(16),
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.95),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                    const Expanded(
                      child: Text(
                        'Suivi du vidangeur',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.my_location),
                      onPressed: _fitBothMarkers,
                    ),
                    IconButton(
                      icon: const Icon(Icons.refresh),
                      onPressed: _forceRefresh,
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Loading indicator
          if (_isLoading)
            const Center(
              child: Card(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircularProgressIndicator(),
                      SizedBox(height: 16),
                      Text('Recherche du vidangeur...'),
                    ],
                  ),
                ),
              ),
            ),

          // Error message
          if (_hasError)
            Center(
              child: Card(
                margin: const EdgeInsets.all(32),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.error_outline,
                          size: 48, color: AppColors.danger),
                      const SizedBox(height: 16),
                      Text(_errorMessage ?? 'Une erreur est survenue'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _isLoading = true;
                            _hasError = false;
                            _useWebSocket = true; // Try WebSocket again
                          });
                          _startTracking();
                        },
                        child: const Text('Reessayer'),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Bottom info card
          if (_currentPosition != null)
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: SafeArea(
                top: false,
                child: _buildInfoCard(),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildInfoCard() {
    final position = _currentPosition!;

    return Container(
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.15),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (!position.isFresh)
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text(
                  'Position ancienne — en attente d’un nouveau signal GPS.',
                  style: TextStyle(color: AppColors.textMuted)),
            ),
          // Arriving soon banner
          if (position.isArriving)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 8),
              decoration: const BoxDecoration(
                color: AppColors.secondary,
                borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.check_circle, color: Colors.white, size: 20),
                  SizedBox(width: 8),
                  Text(
                    'Arrivee imminente !',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // Driver info row
                Row(
                  children: [
                    // Driver avatar
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: AppColors.primary100,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(
                        Icons.local_shipping,
                        color: AppColors.primary,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Driver name
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            position.driverName,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(
                                _useWebSocket ? Icons.wifi : Icons.sync,
                                size: 14,
                                color: _useWebSocket
                                    ? AppColors.secondary
                                    : AppColors.textMuted,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                _connectionMode,
                                style: TextStyle(
                                  color: _useWebSocket
                                      ? AppColors.secondary
                                      : AppColors.textMuted,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    // Call button
                    if (position.driverPhone != null)
                      Container(
                        decoration: BoxDecoration(
                          color: AppColors.secondary100,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: IconButton(
                          icon: const Icon(Icons.phone,
                              color: AppColors.secondary),
                          onPressed: _callDriver,
                        ),
                      ),
                  ],
                ),

                const SizedBox(height: 20),

                // ETA and Distance
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.sand,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      // ETA
                      Expanded(
                        child: Column(
                          children: [
                            const Icon(
                              Icons.schedule,
                              color: AppColors.primary,
                              size: 28,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              position.formattedEta,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Durée routière',
                              style: TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      // Divider
                      Container(
                        width: 1,
                        height: 60,
                        color: AppColors.border,
                      ),
                      // Distance
                      Expanded(
                        child: Column(
                          children: [
                            const Icon(
                              Icons.route,
                              color: AppColors.secondary,
                              size: 28,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              position.formattedDistance,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'À vol d’oiseau',
                              style: TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
