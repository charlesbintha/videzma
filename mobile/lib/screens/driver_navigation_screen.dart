import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart' as geo;
import 'package:url_launcher/url_launcher.dart';
import '../models/navigation_route.dart' as nav;
import '../services/navigation_service.dart';
import '../services/navigation_progress.dart';
import '../theme/app_colors.dart';

/// Navigation screen for drivers to navigate to client location
class DriverNavigationScreen extends StatefulWidget {
  final int serviceRequestId;
  final int clientId;
  final String clientName;
  final String? clientPhone;
  final String? clientAddress;
  final double clientLatitude;
  final double clientLongitude;

  const DriverNavigationScreen({
    super.key,
    required this.serviceRequestId,
    required this.clientId,
    required this.clientName,
    this.clientPhone,
    this.clientAddress,
    required this.clientLatitude,
    required this.clientLongitude,
  });

  @override
  State<DriverNavigationScreen> createState() => _DriverNavigationScreenState();
}

class _DriverNavigationScreenState extends State<DriverNavigationScreen> {
  GoogleMapController? _mapController;
  final NavigationService _navigationService = NavigationService();
  StreamSubscription<geo.Position>? _positionSubscription;

  nav.NavigationRoute? _currentRoute;
  LatLng? _driverPosition;
  bool _isLoading = true;
  bool _isNavigating = false;
  String? _errorMessage;
  int _currentStepIndex = 0;
  NavigationProgress? _progress;
  double? _stepRemaining;
  geo.Position? _latestPosition, _pendingUpload;
  bool _uploading = false, _refreshing = false, _arrivalShown = false;
  bool _followDriver = true, _stopping = false;
  String? _gpsStatus, _networkStatus;
  DateTime? _lastRefresh, _lastCamera, _lastUpload;
  nav.LatLng? _refreshOrigin;
  int _offRouteFixes = 0;
  Timer? _gpsWatchdog;

  // Distance tracking
  double? _remainingDistance;
  int? _remainingEta;

  @override
  void initState() {
    super.initState();
    _startNavigation();
  }

  @override
  void dispose() {
    _gpsWatchdog?.cancel();
    _mapController?.dispose();
    _positionSubscription?.cancel();
    if (_isNavigating) {
      _navigationService.stopNavigation();
    }
    _navigationService.dispose();
    super.dispose();
  }

  Future<void> _startNavigation() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _latestPosition = null;
      _gpsStatus = null;
      _networkStatus = null;
      _arrivalShown = false;
      _currentStepIndex = 0;
      _stepRemaining = null;
    });

    final result = await _navigationService.startNavigation(
      clientId: widget.clientId,
      serviceRequestId: widget.serviceRequestId,
    );

    if (!mounted) return;

    if (result.success && result.route != null) {
      setState(() {
        _currentRoute = result.route;
        _progress = NavigationProgress(result.route!);
        _lastRefresh = DateTime.now();
        _isNavigating = true;
        _isLoading = false;
        _remainingDistance = result.route!.distanceMeters;
        _remainingEta = result.route!.etaMinutes;
      });

      // Start listening to position updates
      _startPositionUpdates();
    } else {
      setState(() {
        _isLoading = false;
        _errorMessage =
            result.message ?? 'Impossible de demarrer la navigation';
      });
    }
  }

  void _startPositionUpdates() {
    _positionSubscription?.cancel();
    _positionSubscription = _navigationService.positionStream.listen(
      _onPosition,
      onError: (Object error) {
        if (mounted) {
          setState(() => _gpsStatus =
              'GPS indisponible : vérifiez la localisation du téléphone');
        }
      },
    );
    final initial = _navigationService.currentPosition;
    if (initial != null) _onPosition(initial);
    _gpsWatchdog?.cancel();
    _gpsWatchdog = Timer.periodic(const Duration(seconds: 5), (_) {
      if (!mounted || !_isNavigating) return;
      if (_latestPosition == null ||
          DateTime.now().difference(_latestPosition!.timestamp).inSeconds >
              20) {
        setState(() => _gpsStatus =
            'Signal GPS perdu : en attente d’une position précise');
      }
    });
  }

  void _onPosition(geo.Position position) {
    if (!mounted || !_isNavigating || _stopping) return;
    final now = DateTime.now();
    if (now.difference(position.timestamp).inSeconds > 15 ||
        (_latestPosition != null &&
            !position.timestamp.isAfter(_latestPosition!.timestamp))) {
      return;
    }
    if (!position.accuracy.isFinite || position.accuracy > 50) {
      setState(() => _gpsStatus = 'GPS imprécis : rejoignez un endroit dégagé');
      return;
    }
    _latestPosition = position;
    final fix =
        nav.LatLng(latitude: position.latitude, longitude: position.longitude);
    final progress = _progress?.locate(fix);
    setState(() {
      _gpsStatus = null;
      _driverPosition = LatLng(position.latitude, position.longitude);
      if (progress != null && progress.offRouteMeters <= 60) {
        _currentStepIndex = progress.stepIndex;
        _stepRemaining = progress.stepRemainingMeters;
        _remainingDistance = progress.remainingMeters;
        _remainingEta = progress.etaMinutes;
      }
    });
    // Camera and guidance never wait for the server.
    if (_followDriver &&
        (_lastCamera == null ||
            now.difference(_lastCamera!).inMilliseconds >= 1500)) {
      _lastCamera = now;
      _mapController
          ?.animateCamera(CameraUpdate.newCameraPosition(CameraPosition(
        target: _driverPosition!,
        zoom: 17,
        bearing: position.speed > 1 &&
                position.heading.isFinite &&
                position.heading >= 0
            ? position.heading
            : 0,
      )));
    }
    if (_lastUpload == null || now.difference(_lastUpload!).inSeconds >= 5) {
      _lastUpload = now;
      _pendingUpload = position;
      unawaited(_uploadPosition());
    }
    _offRouteFixes = progress != null && progress.offRouteMeters > 60
        ? _offRouteFixes + 1
        : 0;
    _refreshOrigin ??= fix;
    final elapsed =
        _lastRefresh == null ? 999 : now.difference(_lastRefresh!).inSeconds;
    final moved = NavigationProgress.distance(_refreshOrigin!, fix);
    if (!_refreshing &&
        elapsed >= 20 &&
        (_offRouteFixes >= 2 || (elapsed >= 60 && moved >= 50))) {
      unawaited(_refreshRoute());
    }
    final destination = nav.LatLng(
        latitude: widget.clientLatitude, longitude: widget.clientLongitude);
    if (!_arrivalShown &&
        position.accuracy <= 30 &&
        progress != null &&
        progress.offRouteMeters <= 40 &&
        progress.remainingMeters < 80 &&
        NavigationProgress.distance(fix, destination) < 40) {
      _showArrivedDialog();
    }
  }

  Future<void> _uploadPosition() async {
    if (_uploading) return;
    _uploading = true;
    try {
      while (mounted && _isNavigating && !_stopping && _pendingUpload != null) {
        final position = _pendingUpload!;
        _pendingUpload = null;
        final result =
            await _navigationService.updatePositionOnServer(position);
        if (!mounted || _stopping) return;
        setState(() {
          _networkStatus = result.success
              ? null
              : 'Connexion interrompue : position non synchronisée';
          if (!_navigationService.isNavigating) {
            _isNavigating = false;
            _errorMessage =
                'Cette mission ne permet plus la navigation. Revenez à la fiche de la mission.';
          }
        });
      }
    } finally {
      _uploading = false;
    }
  }

  Future<void> _refreshRoute() async {
    if (!_isNavigating || _refreshing || _stopping) return;
    final position = _latestPosition;
    if (position == null ||
        DateTime.now().difference(position.timestamp).inSeconds > 15) {
      setState(() => _gpsStatus = 'En attente d’une position GPS récente');
      return;
    }
    setState(() => _refreshing = true);
    _lastRefresh = DateTime.now();
    final result = await _navigationService.updateRoute(
      clientId: widget.clientId,
      serviceRequestId: widget.serviceRequestId,
      position: position,
    );
    if (!mounted || _stopping) return;
    setState(() {
      _refreshing = false;
      if (result.success && result.route != null) {
        _currentRoute = result.route;
        _progress = NavigationProgress(result.route!);
        _currentStepIndex = 0;
        _stepRemaining = null;
        _remainingDistance = result.route!.distanceMeters;
        _remainingEta = result.route!.etaMinutes;
        _refreshOrigin = nav.LatLng(
            latitude: position.latitude, longitude: position.longitude);
        _offRouteFixes = 0;
        _networkStatus = null;
        // A newer GPS fix may have arrived while Google calculated the route.
        final latest = _latestPosition!;
        final progress = _progress!.locate(
            nav.LatLng(latitude: latest.latitude, longitude: latest.longitude));
        if (progress != null && progress.offRouteMeters <= 60) {
          _currentStepIndex = progress.stepIndex;
          _stepRemaining = progress.stepRemainingMeters;
          _remainingDistance = progress.remainingMeters;
          _remainingEta = progress.etaMinutes;
        }
      } else {
        _networkStatus = 'Recalcul indisponible : dernier trajet conservé';
      }
    });
  }

  void _showArrivedDialog() {
    _arrivalShown = true;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.check_circle, color: AppColors.secondary, size: 28),
            SizedBox(width: 12),
            Text('Arrive a destination'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Vous etes arrive chez ${widget.clientName}',
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 16),
            if (widget.clientAddress != null)
              Text(
                widget.clientAddress!,
                style: TextStyle(color: AppColors.textMuted),
              ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: const Text('Continuer'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _stopNavigation();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.secondary,
            ),
            child: const Text('Terminer navigation'),
          ),
        ],
      ),
    );
  }

  Future<void> _stopNavigation() async {
    if (_stopping) return;
    _stopping = true;
    _isNavigating = false;
    _pendingUpload = null;
    await _navigationService.stopNavigation();
    if (mounted) {
      Navigator.of(context)
          .pop(true); // Return true to indicate navigation completed
    }
  }

  Future<void> _callClient() async {
    if (widget.clientPhone == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Numero de telephone non disponible')),
      );
      return;
    }

    final uri = Uri.parse('tel:${widget.clientPhone}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _openInExternalMaps() async {
    final url = 'https://www.google.com/maps/dir/?api=1'
        '&destination=${widget.clientLatitude},${widget.clientLongitude}'
        '&travelmode=driving&dir_action=navigate';
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final clientPosition =
        LatLng(widget.clientLatitude, widget.clientLongitude);

    // Build markers
    final markers = <Marker>{
      // Client destination marker
      Marker(
        markerId: const MarkerId('client'),
        position: clientPosition,
        icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
        infoWindow: InfoWindow(
          title: widget.clientName,
          snippet: widget.clientAddress,
        ),
      ),
    };

    // Add driver marker if we have position
    if (_driverPosition != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('driver'),
          position: _driverPosition!,
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue),
          infoWindow: const InfoWindow(title: 'Ma position'),
        ),
      );
    }

    // Build polyline from route
    final polylines = <Polyline>{};
    if (_currentRoute?.polyline != null) {
      final points = (_progress?.points ?? <nav.LatLng>[])
          .map((p) => LatLng(p.latitude, p.longitude))
          .toList();
      polylines.add(
        Polyline(
          polylineId: const PolylineId('route'),
          points: points,
          color: AppColors.primary,
          width: 5,
        ),
      );
    }

    return Scaffold(
      body: Stack(
        children: [
          // Map
          Listener(
            onPointerDown: (_) => _followDriver = false,
            child: GoogleMap(
              initialCameraPosition: CameraPosition(
                target: _driverPosition ?? clientPosition,
                zoom: 14,
              ),
              markers: markers,
              polylines: polylines,
              onMapCreated: (controller) {
                _mapController = controller;
              },
              myLocationEnabled: true,
              myLocationButtonEnabled: false,
              zoomControlsEnabled: false,
              mapToolbarEnabled: false,
            ),
          ),

          // Top navigation bar
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              child: Container(
                margin: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 10,
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Header with back button
                    Padding(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      child: Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.close),
                            onPressed: () => _confirmStopNavigation(),
                          ),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.clientName,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                                if (widget.clientAddress != null)
                                  Text(
                                    widget.clientAddress!,
                                    style: TextStyle(
                                      color: AppColors.textMuted,
                                      fontSize: 12,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.phone,
                                color: AppColors.secondary),
                            onPressed: _callClient,
                          ),
                          IconButton(
                            icon: const Icon(Icons.map_outlined),
                            onPressed: _openInExternalMaps,
                          ),
                        ],
                      ),
                    ),

                    if (_gpsStatus != null ||
                        _networkStatus != null ||
                        _refreshing)
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                        child: Text(
                            _gpsStatus ??
                                (_refreshing
                                    ? 'Recalcul du trajet…'
                                    : _networkStatus!),
                            style: const TextStyle(
                                fontSize: 14, color: AppColors.textMuted)),
                      ),
                    // Current step instruction
                    if (_currentRoute?.routeData?.legs.isNotEmpty == true)
                      _buildCurrentInstruction(),
                  ],
                ),
              ),
            ),
          ),

          // Loading overlay
          if (_isLoading)
            Container(
              color: Colors.black45,
              child: const Center(
                child: Card(
                  child: Padding(
                    padding: EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircularProgressIndicator(),
                        SizedBox(height: 16),
                        Text('Calcul de l\'itineraire...'),
                      ],
                    ),
                  ),
                ),
              ),
            ),

          // Error message
          if (_errorMessage != null)
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
                      Text(_errorMessage!),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _startNavigation,
                        child: const Text('Reessayer'),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Bottom info panel
          if (_isNavigating && !_isLoading)
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: SafeArea(
                top: false,
                child: _buildBottomPanel(),
              ),
            ),

          // Floating buttons
          if (_isNavigating)
            Positioned(
              right: 16,
              bottom: 200,
              child: Column(
                children: [
                  FloatingActionButton.small(
                    heroTag: 'center',
                    onPressed: () {
                      _followDriver = true;
                      if (_driverPosition != null) {
                        _mapController?.animateCamera(
                          CameraUpdate.newLatLngZoom(_driverPosition!, 16),
                        );
                      }
                    },
                    child: const Icon(Icons.my_location),
                  ),
                  const SizedBox(height: 8),
                  FloatingActionButton.small(
                    heroTag: 'refresh',
                    onPressed: _refreshRoute,
                    child: const Icon(Icons.refresh),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCurrentInstruction() {
    final legs = _currentRoute!.routeData!.legs;
    if (legs.isEmpty) return const SizedBox.shrink();

    final steps = legs.expand((leg) => leg.steps).toList();
    if (steps.isEmpty || _currentStepIndex >= steps.length) {
      return const SizedBox.shrink();
    }

    final step = steps[_currentStepIndex];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.primary100,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              _getManeuverIcon(step.maneuver),
              color: AppColors.primary,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  step.instruction,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  _formatDistance(
                      _stepRemaining ?? step.distanceMeters.toDouble()),
                  style: TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomPanel() {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 20,
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ETA and Distance
          Row(
            children: [
              Expanded(
                child: _buildInfoTile(
                  icon: Icons.schedule,
                  iconColor: AppColors.primary,
                  value: _formatEta(
                      _remainingEta ?? _currentRoute?.etaMinutes ?? 0),
                  label: 'Arrivee estimee',
                ),
              ),
              Container(
                width: 1,
                height: 50,
                color: AppColors.border,
              ),
              Expanded(
                child: _buildInfoTile(
                  icon: Icons.route,
                  iconColor: AppColors.secondary,
                  value: _formatDistance(
                      _remainingDistance ?? _currentRoute?.distanceMeters ?? 0),
                  label: 'Distance',
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Arrived button
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton.icon(
              onPressed: _showArrivedDialog,
              icon: const Icon(Icons.check_circle_outline),
              label: const Text('Je suis arrive'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.secondary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoTile({
    required IconData icon,
    required Color iconColor,
    required String value,
    required String label,
  }) {
    return Column(
      children: [
        Icon(icon, color: iconColor, size: 24),
        const SizedBox(height: 8),
        Text(
          value,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            color: AppColors.textMuted,
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  void _confirmStopNavigation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Arreter la navigation ?'),
        content: const Text(
            'Etes-vous sur de vouloir arreter la navigation vers ce client ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _stopNavigation();
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Arreter'),
          ),
        ],
      ),
    );
  }

  IconData _getManeuverIcon(String? maneuver) {
    switch (maneuver) {
      case 'turn-left':
        return Icons.turn_left;
      case 'turn-right':
        return Icons.turn_right;
      case 'turn-sharp-left':
        return Icons.turn_sharp_left;
      case 'turn-sharp-right':
        return Icons.turn_sharp_right;
      case 'turn-slight-left':
        return Icons.turn_slight_left;
      case 'turn-slight-right':
        return Icons.turn_slight_right;
      case 'uturn-left':
      case 'uturn-right':
        return Icons.u_turn_left;
      case 'roundabout-left':
      case 'roundabout-right':
        return Icons.roundabout_left;
      case 'merge':
        return Icons.merge;
      case 'fork-left':
      case 'fork-right':
        return Icons.fork_left;
      case 'ramp-left':
      case 'ramp-right':
        return Icons.ramp_left;
      default:
        return Icons.arrow_upward;
    }
  }

  String _formatDistance(double meters) {
    if (meters < 1000) {
      return '${meters.toInt()} m';
    }
    return '${(meters / 1000).toStringAsFixed(1)} km';
  }

  String _formatEta(int minutes) {
    if (minutes < 1) return 'Maintenant';
    if (minutes < 60) return '$minutes min';
    final hours = minutes ~/ 60;
    final mins = minutes % 60;
    if (mins == 0) return '${hours}h';
    return '${hours}h ${mins}m';
  }
}
