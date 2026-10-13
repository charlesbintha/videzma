import "package:pointer_interceptor/pointer_interceptor.dart";
import "package:flutter/foundation.dart";
import "dart:async";
import "package:flutter/material.dart";
import "package:google_maps_flutter/google_maps_flutter.dart";
import "package:geolocator/geolocator.dart";
import "../theme/app_colors.dart";
import "../services/location_store.dart";
import "../services/location_service.dart";
import "location_history_screen.dart";

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  GoogleMapController? controller;
  StreamSubscription<Position>? subscription;
  bool tracking = false;
  bool isUpdating = false;
  DateTime? lastSentAt;
  Timer? firstFixTimer;
  final locationService = LocationService();
  String statusMessage = 'Partage en pause';
  bool sending = false;
  int generation = 0;
  final sendInterval = const Duration(seconds: 30);

  @override
  void dispose() {
    generation++;
    firstFixTimer?.cancel();
    subscription?.cancel();
    controller?.dispose();
    super.dispose();
  }

  void _stopSharing([String message = 'Partage en pause']) {
    generation++;
    firstFixTimer?.cancel();
    subscription?.cancel();
    subscription = null;
    if (mounted)
      setState(() {
        tracking = false;
        statusMessage = message;
      });
  }

  Future<void> _startTracking() async {
    final current = ++generation;
    lastSentAt = null;
    setState(() => statusMessage = 'Recherche du signal GPS…');
    firstFixTimer?.cancel();
    firstFixTimer = Timer(const Duration(seconds: 20), () {
      if (mounted && generation == current) {
        _stopSharing(
            'Aucun signal GPS reçu. Vérifiez les autorisations de localisation.');
      }
    });
    final stream = await locationService.positionStream();
    if (!mounted || !tracking || current != generation) return;
    if (stream == null) {
      _stopSharing(locationService.lastError ?? 'Localisation indisponible.');
      return;
    }
    subscription?.cancel();
    subscription = stream.listen((position) async {
      if (!mounted || !tracking || current != generation) return;
      firstFixTimer?.cancel();
      final first = lastSentAt == null;
      LocationStore.set(position.latitude, position.longitude);
      if (first)
        controller?.animateCamera(CameraUpdate.newLatLngZoom(
            LatLng(position.latitude, position.longitude), 15));
      setState(() {});
      if (sending ||
          (lastSentAt != null &&
              DateTime.now().difference(lastSentAt!) < sendInterval)) return;
      sending = true;
      setState(() => statusMessage = 'Position trouvée. Envoi en cours…');
      final sent = await locationService.sendPosition(
        latitude: position.latitude,
        longitude: position.longitude,
        capturedAt: position.timestamp,
      );
      sending = false;
      if (!mounted || !tracking || current != generation) return;
      if (sent) lastSentAt = DateTime.now();
      setState(() => statusMessage = sent
          ? 'Position enregistrée et partage actif'
          : locationService.lastError ?? 'Envoi impossible.');
    }, onError: (Object error) {
      if (mounted && current == generation)
        _stopSharing(
            'Signal GPS indisponible. Vérifiez les autorisations et réessayez.');
    }, onDone: () {
      if (mounted && current == generation)
        _stopSharing('Suivi GPS interrompu.');
    });
  }

  Future<void> _sendOnce() async {
    setState(() {
      isUpdating = true;
      statusMessage = 'Recherche du signal GPS…';
    });
    final sent = await locationService.captureAndSend();
    if (!mounted) return;
    setState(() {
      isUpdating = false;
      statusMessage = sent
          ? 'Position enregistrée'
          : locationService.lastError ?? 'Position indisponible.';
    });
    if (sent && LocationStore.hasLocation) {
      controller?.animateCamera(CameraUpdate.newLatLngZoom(
          LatLng(LocationStore.latitude!, LocationStore.longitude!), 15));
    }
  }

  @override
  Widget build(BuildContext context) {
    final lat = LocationStore.latitude ?? 14.6937;
    final lng = LocationStore.longitude ?? -17.4441;
    final position = LatLng(lat, lng);

    return Scaffold(
      backgroundColor: AppColors.sand,
      body: SafeArea(
        child: Stack(
          children: [
            GoogleMap(
              initialCameraPosition: CameraPosition(
                target: position,
                zoom: 14,
              ),
              markers: {
                if (LocationStore.latitude != null &&
                    LocationStore.longitude != null)
                  Marker(
                    markerId: const MarkerId("client"),
                    position: position,
                  ),
              },
              onMapCreated: (map) => controller = map,
              myLocationEnabled: false,
              zoomControlsEnabled: false,
              mapToolbarEnabled: false,
            ),
            Positioned(
              left: 16,
              right: 16,
              top: 16,
              child: PointerInterceptor(
                  intercepting: kIsWeb,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.94),
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 16,
                          offset: const Offset(0, 8),
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
                            "Carte",
                            textAlign: TextAlign.center,
                            style: TextStyle(
                                fontSize: 18, fontWeight: FontWeight.w700),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.history),
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => const LocationHistoryScreen(),
                              ),
                            );
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.my_location),
                          onPressed: () {
                            controller?.animateCamera(
                              CameraUpdate.newLatLngZoom(position, 15),
                            );
                          },
                        ),
                      ],
                    ),
                  )),
            ),
            Positioned(
              left: 16,
              right: 16,
              bottom: 24,
              child: PointerInterceptor(
                  intercepting: kIsWeb,
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.94),
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 16,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Icon(
                          tracking ? Icons.location_on : Icons.location_off,
                          color:
                              tracking ? AppColors.blue : AppColors.textMuted,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            statusMessage,
                            style: const TextStyle(fontWeight: FontWeight.w600),
                          ),
                        ),
                        if (isUpdating)
                          const Padding(
                            padding: EdgeInsets.only(right: 8),
                            child: SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          ),
                        Switch(
                          value: tracking,
                          activeColor: AppColors.blue,
                          onChanged: isUpdating
                              ? null
                              : (value) {
                                  setState(() {
                                    tracking = value;
                                  });
                                  if (value) {
                                    _startTracking();
                                  } else {
                                    _stopSharing();
                                  }
                                },
                        ),
                        IconButton(
                          icon: const Icon(Icons.send),
                          tooltip: 'Enregistrer ma position une fois',
                          onPressed: isUpdating || tracking ? null : _sendOnce,
                        ),
                      ],
                    ),
                  )),
            ),
          ],
        ),
      ),
    );
  }
}
