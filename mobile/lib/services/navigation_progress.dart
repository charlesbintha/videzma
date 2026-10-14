import 'dart:math' as math;
import '../models/navigation_route.dart';

/// Matches GPS fixes to Google's road geometry, never to a straight line ETA.
class NavigationProgress {
  final NavigationRoute route;
  final List<LatLng> points = [];
  final List<int> _steps = [];
  final List<double> _lengths = [0];
  double? _previousAlong;
  LatLng? _previousFix;
  NavigationProgress(this.route) {
    final steps = route.routeData?.legs.expand((leg) => leg.steps).toList() ??
        <RouteStep>[];
    for (var i = 0; i < steps.length; i++) {
      final decoded = decodePolyline(steps[i].polyline ?? '');
      final geometry = decoded.length >= 2
          ? decoded
          : [steps[i].startLocation, steps[i].endLocation];
      for (final point in geometry) {
        if (points.isNotEmpty && distance(points.last, point) < .1) continue;
        if (points.isNotEmpty) _steps.add(i);
        points.add(point);
      }
    }
    if (points.length < 2) {
      points.clear();
      points.addAll(decodePolyline(route.polyline ?? ''));
      _steps.clear();
      _steps.addAll(List.filled(math.max(0, points.length - 1), 0));
    }
    for (var i = 1; i < points.length; i++) {
      _lengths.add(_lengths.last + distance(points[i - 1], points[i]));
    }
  }

  RouteProgress? locate(LatLng fix) {
    if (points.length < 2 || _lengths.last == 0) return null;
    var bestDistance = double.infinity;
    var along = 0.0;
    var segment = 0;
    final allowance = _previousFix == null
        ? double.infinity
        : distance(_previousFix!, fix) + 100;
    for (var i = 0; i < points.length - 1; i++) {
      final a = points[i], b = points[i + 1];
      final scale = math.cos(fix.latitude * math.pi / 180);
      final ax = (a.longitude - fix.longitude) * scale * 111195;
      final ay = (a.latitude - fix.latitude) * 111195;
      final dx = (b.longitude - a.longitude) * scale * 111195;
      final dy = (b.latitude - a.latitude) * 111195;
      final squared = dx * dx + dy * dy;
      final t =
          squared == 0 ? 0.0 : (-(ax * dx + ay * dy) / squared).clamp(0.0, 1.0);
      final candidate = _lengths[i] + t * (_lengths[i + 1] - _lengths[i]);
      // Avoid jumping to a later crossing of the same road.
      if (_previousAlong != null &&
          (candidate - _previousAlong!).abs() > allowance) {
        continue;
      }
      final gap =
          math.sqrt(math.pow(ax + t * dx, 2) + math.pow(ay + t * dy, 2));
      if (gap < bestDistance) {
        bestDistance = gap;
        along = candidate;
        segment = i;
      }
    }
    if (!bestDistance.isFinite) return null;
    if (bestDistance <= 60) {
      _previousAlong = along;
      _previousFix = fix;
    }
    final ratio = (1 - along / _lengths.last).clamp(0.0, 1.0);
    var end = segment + 1;
    while (end < _steps.length && _steps[end] == _steps[segment]) {
      end++;
    }
    return RouteProgress(
      stepIndex: _steps[segment],
      offRouteMeters: bestDistance,
      remainingMeters: route.distanceMeters * ratio,
      etaMinutes: math.max(1, (route.etaMinutes * ratio).ceil()),
      stepRemainingMeters: math.max(0, _lengths[end] - along),
    );
  }

  static double distance(LatLng a, LatLng b) {
    final lat = (a.latitude + b.latitude) / 2 * math.pi / 180;
    return 111195 *
        math.sqrt(math.pow(a.latitude - b.latitude, 2) +
            math.pow((a.longitude - b.longitude) * math.cos(lat), 2));
  }

  static List<LatLng> decodePolyline(String encoded) {
    final points = <LatLng>[];
    var index = 0, latitude = 0, longitude = 0;
    int component() {
      var result = 0, multiplier = 1;
      while (true) {
        if (index >= encoded.length || multiplier > 1073741824) {
          throw const FormatException('Invalid polyline');
        }
        final value = encoded.codeUnitAt(index++) - 63;
        if (value < 0 || value > 63) {
          throw const FormatException('Invalid polyline');
        }
        // Arithmetic keeps negative deltas identical on Dart VM and JavaScript.
        result += (value % 32) * multiplier;
        multiplier *= 32;
        if (value < 32) {
          final magnitude = result ~/ 2;
          return result.isOdd ? -magnitude - 1 : magnitude;
        }
      }
    }

    try {
      while (index < encoded.length) {
        latitude += component();
        longitude += component();
        if (latitude.abs() > 9000000 || longitude.abs() > 18000000) {
          throw const FormatException('Polyline coordinates out of range');
        }
        points
            .add(LatLng(latitude: latitude / 1e5, longitude: longitude / 1e5));
      }
      return points;
    } on FormatException {
      return [];
    }
  }
}

class RouteProgress {
  final int stepIndex, etaMinutes;
  final double offRouteMeters, remainingMeters, stepRemainingMeters;
  RouteProgress(
      {required this.stepIndex,
      required this.etaMinutes,
      required this.offRouteMeters,
      required this.remainingMeters,
      required this.stepRemainingMeters});
}
