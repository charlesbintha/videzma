import 'package:flutter/material.dart';
import 'package:videzma_mobile/widgets/app_map.dart';
import 'package:videzma_mobile/services/navigation_progress.dart';

// Public Dakar route returned by the production routing service, 14 October 2026.
void main() {
  final points = NavigationProgress.decodePolyline(
          r'autxA|pniBSSgAhAeAfAo@p@f@@l@HpBDLFHHDLy@l@qAfA]V[XUP{ExDsCxBkBbBe@f@mAnAYSKHMPeAz@kCrBcAn@kA|@qB~AkA`ASLONMD]`@qDpCwC~BSNy@f@mCxByAjA{@r@y@p@_At@w@p@y@n@u@j@y@n@aAv@gA|@aAv@_Az@g@`@g@d@WTeAdAQLgCrBu@h@_Aj@uAhAQLwDxCe@l@gDpCaBvAcD~B_E~CiAp@cC`A}Bh@aATa@Da@?mAGgCKoBOu@Ka@CKDIL@VJHv@Pd@?j@?l@@h@XBNXt@DHt@hB')
      .map((p) => LatLng(p.latitude, p.longitude))
      .toList();
  runApp(MaterialApp(
      debugShowCheckedModeBanner: false,
      home: Scaffold(
          body: SafeArea(
              child: Center(
                  child: SizedBox(
                      width: 480,
                      child: Column(children: [
                        const Padding(
                            padding: EdgeInsets.all(16),
                            child: Text(
                                'Trajet de test à Dakar · 4,12 km · 7 min\nCalcul réel depuis le serveur de production',
                                textAlign: TextAlign.center)),
                        Expanded(
                            child: AppMap(
                                initialCameraPosition: const MapCameraPosition(
                                    target: LatLng(14.7047, -17.4572),
                                    zoom: 14),
                                markers: {
                                  MapMarker(
                                      id: 'start',
                                      position: points.first,
                                      title: 'Départ du test',
                                      color: Colors.green),
                                  MapMarker(
                                      id: 'end',
                                      position: points.last,
                                      title: 'Arrivée du test')
                                },
                                polylines: {
                                  MapLine(
                                      id: 'road',
                                      points: points,
                                      color: Colors.blue)
                                },
                                zoomControlsEnabled: true)),
                        const Padding(
                            padding: EdgeInsets.all(12),
                            child: Text(
                                '13 instructions · © openrouteservice.org by HeiGIT',
                                textAlign: TextAlign.center)),
                      ])))))));
}
