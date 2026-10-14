import 'package:flutter/material.dart';
import 'package:videzma_mobile/widgets/app_map.dart';

// Manual tile-rendering check with public landmarks only, no production account.
void main() => runApp(const MaterialApp(home: Scaffold(
  body: SafeArea(child: Center(child: SizedBox(width: 480, child: AppMap(
    initialCameraPosition: MapCameraPosition(target: LatLng(14.6928, -17.4467), zoom: 14),
    markers: {MapMarker(id: 'preview', position: LatLng(14.6928, -17.4467), title: 'Aperçu OpenStreetMap — Dakar')},
    zoomControlsEnabled: true,
  )))),
)));
