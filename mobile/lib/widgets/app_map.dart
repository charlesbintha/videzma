import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart' as fm;
import 'package:latlong2/latlong.dart';
import 'map_attribution.dart';
export 'package:latlong2/latlong.dart' show LatLng;

class MapCameraPosition {
  final LatLng target;
  final double zoom, bearing;
  const MapCameraPosition(
      {required this.target, this.zoom = 14, this.bearing = 0});
}

class MapBounds {
  final LatLng southwest, northeast;
  const MapBounds({required this.southwest, required this.northeast});
}

class MapCameraUpdate {
  final LatLng? target;
  final double? zoom, bearing;
  final MapBounds? bounds;
  final double padding;
  const MapCameraUpdate._(
      {this.target, this.zoom, this.bearing, this.bounds, this.padding = 0});
  factory MapCameraUpdate.newLatLngZoom(LatLng point, double zoom) =>
      MapCameraUpdate._(target: point, zoom: zoom);
  factory MapCameraUpdate.newCameraPosition(MapCameraPosition camera) =>
      MapCameraUpdate._(
          target: camera.target, zoom: camera.zoom, bearing: camera.bearing);
  factory MapCameraUpdate.newLatLngBounds(MapBounds bounds, double padding) =>
      MapCameraUpdate._(bounds: bounds, padding: padding);
}

class AppMapController {
  final fm.MapController _controller;
  bool _disposed = false;
  AppMapController._(this._controller);
  Future<void> animateCamera(MapCameraUpdate update) async {
    if (_disposed) return;
    if (update.bounds != null) {
      _controller.fitCamera(fm.CameraFit.bounds(
        bounds:
            fm.LatLngBounds(update.bounds!.southwest, update.bounds!.northeast),
        padding: EdgeInsets.all(update.padding),
        maxZoom: 17,
      ));
    } else if (update.target != null) {
      _controller.moveAndRotate(update.target!,
          update.zoom ?? _controller.camera.zoom, -(update.bearing ?? 0));
    }
  }

  // The widget owns the actual controller and closes it once.
  void dispose() => _disposed = true;
}

class MapMarker {
  final String id;
  final LatLng position;
  final Color color;
  final String? title, snippet;
  const MapMarker(
      {required this.id,
      required this.position,
      this.color = Colors.blue,
      this.title,
      this.snippet});
}

class MapLine {
  final String id;
  final List<LatLng> points;
  final Color color;
  final int width;
  const MapLine(
      {required this.id,
      required this.points,
      required this.color,
      this.width = 5});
}

/// Shared OpenStreetMap view: no Google key, native SDK or web script required.
class AppMap extends StatefulWidget {
  final MapCameraPosition initialCameraPosition;
  final Set<MapMarker> markers;
  final Set<MapLine> polylines;
  final ValueChanged<AppMapController>? onMapCreated;
  final ValueChanged<LatLng>? onTap;
  final bool zoomControlsEnabled;
  const AppMap(
      {super.key,
      required this.initialCameraPosition,
      this.markers = const {},
      this.polylines = const {},
      this.onMapCreated,
      this.onTap,
      this.zoomControlsEnabled = false});
  @override
  State<AppMap> createState() => _AppMapState();
}

class _AppMapState extends State<AppMap> {
  final _controller = fm.MapController();
  late final _bridge = AppMapController._(_controller);
  @override
  void dispose() {
    _bridge.dispose();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(children: [
      fm.FlutterMap(
        mapController: _controller,
        options: fm.MapOptions(
          initialCenter: widget.initialCameraPosition.target,
          initialZoom: widget.initialCameraPosition.zoom,
          minZoom: 3,
          maxZoom: 19,
          onMapReady: () => widget.onMapCreated?.call(_bridge),
          onTap: (_, point) => widget.onTap?.call(point),
        ),
        children: [
          fm.TileLayer(
            urlTemplate: const String.fromEnvironment('MAP_TILE_URL',
                defaultValue: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
            userAgentPackageName: 'com.videzma.app',
            maxNativeZoom: 19,
            // flutter_map's native disk cache honours HTTP cache headers.
          ),
          fm.PolylineLayer(
              polylines: widget.polylines
                  .map((line) => fm.Polyline(
                      points: line.points,
                      color: line.color,
                      strokeWidth: line.width.toDouble()))
                  .toList()),
          fm.MarkerLayer(
              markers: widget.markers
                  .map((marker) => fm.Marker(
                        key: ValueKey(marker.id),
                        point: marker.position,
                        width: 44,
                        height: 44,
                        child: Tooltip(
                            message: [marker.title, marker.snippet]
                                .whereType<String>()
                                .join('\n'),
                            child: Icon(Icons.location_on,
                                color: marker.color,
                                size: 40,
                                shadows: const [
                                  Shadow(color: Colors.white, blurRadius: 4)
                                ])),
                      ))
                  .toList()),
        ],
      ),
      // Stay clear of the floating mission panels at the top and bottom.
      const Align(
        alignment: Alignment.centerLeft,
        child: MapAttribution(),
      ),
      if (widget.zoomControlsEnabled)
        Positioned(
            right: 8,
            top: 8,
            child: Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                child: Column(children: [
                  IconButton(
                      onPressed: () => _controller.move(
                          _controller.camera.center,
                          (_controller.camera.zoom + 1).clamp(3, 19)),
                      icon: const Icon(Icons.add)),
                  IconButton(
                      onPressed: () => _controller.move(
                          _controller.camera.center,
                          (_controller.camera.zoom - 1).clamp(3, 19)),
                      icon: const Icon(Icons.remove)),
                ]))),
    ]);
  }
}
