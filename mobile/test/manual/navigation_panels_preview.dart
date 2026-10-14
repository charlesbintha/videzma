import 'package:flutter/material.dart';
import 'package:videzma_mobile/screens/driver_navigation_screen.dart';
import 'package:videzma_mobile/services/navigation_service.dart';
import 'package:videzma_mobile/models/navigation_route.dart';
import '../fixtures/dakar_polyline.dart';

// Isolated UI preview: no mission or location is written to the server.
class PreviewNavigationService extends NavigationService {
  @override
  Future<NavigationResult> startNavigation(
      {required int clientId,
      int? serviceRequestId,
      double? originLat,
      double? originLng}) async {
    return NavigationResult(
        success: true,
        route: NavigationRoute(
          sessionId: 0,
          distanceMeters: 4120,
          etaMinutes: 7,
          polyline: dakarPolyline,
          routeData: RouteData(provider: 'openrouteservice', legs: [
            RouteLeg(
              startAddress: 'Dakar',
              endAddress: 'Médina',
              distanceMeters: 4120,
              durationSeconds: 420,
              steps: [
                RouteStep(
                    instruction: 'Démarrez en direction du Sud',
                    distanceMeters: 29,
                    durationSeconds: 10,
                    startLocation:
                        LatLng(latitude: 14.6928, longitude: -17.4467),
                    endLocation: LatLng(latitude: 14.7167, longitude: -17.4677),
                    polyline: dakarPolyline)
              ],
            )
          ]),
        ));
  }

  @override
  Future<void> stopNavigation() async {}
}

void main() => runApp(MaterialApp(
      debugShowCheckedModeBanner: false,
      home: Center(
          child: SizedBox(
              width: 390,
              child: DriverNavigationScreen(
                navigationService: PreviewNavigationService(),
                serviceRequestId: 0,
                clientId: 0,
                clientName: 'Démo — Médina, secteur Grande Mosquée, Dakar',
                clientAddress: 'Médina, secteur Grande Mosquée, Dakar',
                clientLatitude: 14.6928,
                clientLongitude: -17.4467,
              ))),
    ));
