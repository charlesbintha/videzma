import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart' as geo;
import 'package:url_launcher/url_launcher.dart';
import '../models/navigation_route.dart' as nav;
import '../services/navigation_service.dart';
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
    });

    final result = await _navigationService.startNavigation(
      clientId: widget.clientId,
      serviceRequestId: widget.serviceRequestId,
    );

    if (!mounted) return;

    if (result.success && result.route != null) {
      setState(() {
        _currentRoute = result.route;
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
        _errorMessage = result.message ?? 'Impossible de demarrer la navigation';
      });
    }
  }

  void _startPositionUpdates() {
    _positionSubscription?.cancel();
    _positionSubscription = _navigationService.positionStream.listen(
      (position) async {
        if (!mounted) return;

        setState(() {
          _driverPosition = LatLng(position.latitude, position.longitude);
        });

        // Update position on server and get remaining distance/ETA
        final updateResult = await _navigationService.updatePositionOnServer(position);
        if (updateResult.success && mounted) {
          setState(() {
            _remainingDistance = updateResult.distanceMeters;
            _remainingEta = updateResult.etaMinutes;
          });

          // Check if arrived (within 50 meters)
          if (updateResult.distanceMeters != null && updateResult.distanceMeters! < 50) {
            _showArrivedDialog();
          }
        }

        // Center map on driver
        _mapController?.animateCamera(
          CameraUpdate.newLatLng(_driverPosition!),
        );
      },
      onError: (e) {
        debugPrint('Position stream error: $e');
      },
    );
  }

  Future<void> _refreshRoute() async {
    if (!_isNavigating) return;

    final result = await _navigationService.updateRoute(
      clientId: widget.clientId,
      serviceRequestId: widget.serviceRequestId,
    );

    if (result.success && result.route != null && mounted) {
      setState(() {
        _currentRoute = result.route;
        _remainingDistance = result.route!.distanceMeters;
        _remainingEta = result.route!.etaMinutes;
      });
    }
  }

  void _showArrivedDialog() {
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
    await _navigationService.stopNavigation();
    if (mounted) {
      Navigator.of(context).pop(true); // Return true to indicate navigation completed
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
        '&travelmode=driving';
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final clientPosition = LatLng(widget.clientLatitude, widget.clientLongitude);

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
      final points = _decodePolyline(_currentRoute!.polyline!);
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
          GoogleMap(
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
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 10,
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Header with back button
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
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
                            icon: const Icon(Icons.phone, color: AppColors.secondary),
                            onPressed: _callClient,
                          ),
                          IconButton(
                            icon: const Icon(Icons.map_outlined),
                            onPressed: _openInExternalMaps,
                          ),
                        ],
                      ),
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
                      const Icon(Icons.error_outline, size: 48, color: AppColors.danger),
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

    final steps = legs.first.steps;
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
                  '${step.distanceMeters > 1000 ? '${(step.distanceMeters / 1000).toStringAsFixed(1)} km' : '${step.distanceMeters} m'}',
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
            color: Colors.black.withOpacity(0.15),
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
                  value: _formatEta(_remainingEta ?? _currentRoute?.etaMinutes ?? 0),
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
                  value: _formatDistance(_remainingDistance ?? _currentRoute?.distanceMeters ?? 0),
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
        content: const Text('Etes-vous sur de vouloir arreter la navigation vers ce client ?'),
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

  /// Decode Google Maps encoded polyline
  List<LatLng> _decodePolyline(String encoded) {
    List<LatLng> points = [];
    int index = 0;
    int lat = 0;
    int lng = 0;

    while (index < encoded.length) {
      int shift = 0;
      int result = 0;

      int b;
      do {
        b = encoded.codeUnitAt(index++) - 63;
        result |= (b & 0x1F) << shift;
        shift += 5;
      } while (b >= 0x20);

      int dlat = (result & 1) != 0 ? ~(result >> 1) : (result >> 1);
      lat += dlat;

      shift = 0;
      result = 0;

      do {
        b = encoded.codeUnitAt(index++) - 63;
        result |= (b & 0x1F) << shift;
        shift += 5;
      } while (b >= 0x20);

      int dlng = (result & 1) != 0 ? ~(result >> 1) : (result >> 1);
      lng += dlng;

      points.add(LatLng(lat / 1E5, lng / 1E5));
    }

    return points;
  }
}
