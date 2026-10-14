import 'package:flutter_test/flutter_test.dart';
import 'package:videzma_mobile/models/navigation_route.dart';
import 'package:videzma_mobile/services/navigation_progress.dart';

import 'fixtures/dakar_polyline.dart';

LatLng point(double lat, double lng) => LatLng(latitude: lat, longitude: lng);
NavigationRoute route() => NavigationRoute(
      sessionId: 8,
      distanceMeters: 2224,
      etaMinutes: 10,
      routeData: RouteData(legs: [
        RouteLeg(
          startAddress: '',
          endAddress: '',
          distanceMeters: 2224,
          durationSeconds: 600,
          steps: [
            RouteStep(
                instruction: 'Tout droit',
                distanceMeters: 1112,
                durationSeconds: 300,
                startLocation: point(0, 0),
                endLocation: point(0, .01)),
            RouteStep(
                instruction: 'À gauche',
                distanceMeters: 1112,
                durationSeconds: 300,
                startLocation: point(0, .01),
                endLocation: point(.01, .01)),
          ],
        )
      ]),
    );
void main() {
  test('Road distance follows the turn, not the direct distance to the client',
      () {
    final tracker = NavigationProgress(route());
    final progress = tracker.locate(point(0, 0))!;
    expect(progress.remainingMeters, closeTo(2224, 1));
    expect(progress.remainingMeters,
        greaterThan(NavigationProgress.distance(point(0, 0), point(.01, .01))));
    final halfway = tracker.locate(point(0, .005))!;
    expect(halfway.remainingMeters, closeTo(1668, 2));
    expect(halfway.stepRemainingMeters, closeTo(556, 2));
    expect(halfway.etaMinutes, 8);
  });
  test(
      'Instructions advance after the turn and distance reaches zero at arrival',
      () {
    final tracker = NavigationProgress(route());
    tracker.locate(point(0, .0099));
    final turned = tracker.locate(point(.001, .01))!;
    expect(turned.stepIndex, 1);
    expect(turned.offRouteMeters, lessThan(1));
    expect(tracker.locate(point(.01, .01))!.remainingMeters, closeTo(0, 1));
  });
  test('A deviation is detected without consuming route progress', () {
    final tracker = NavigationProgress(route());
    tracker.locate(point(0, .003));
    expect(
        tracker.locate(point(-.002, .003))!.offRouteMeters, greaterThan(200));
    expect(tracker.locate(point(0, .003))!.remainingMeters, closeTo(1890, 3));
  });
  test('Google polyline decoding handles standard and truncated data', () {
    final points =
        NavigationProgress.decodePolyline('_p~iF~ps|U_ulLnnqC_mqNvxq`@');
    expect(points.length, 3);
    expect(points.first.latitude, 38.5);
    expect(points.last.longitude, -126.453);
    expect(NavigationProgress.decodePolyline('_p~iF~ps|U_'), isEmpty);
  });
  test('Production Dakar geometry stays on valid geographic coordinates', () {
    final points = NavigationProgress.decodePolyline(dakarPolyline);
    expect(points.length, greaterThan(80));
    for (final point in points) {
      expect(point.latitude, inInclusiveRange(14.68, 14.73));
      expect(point.longitude, inInclusiveRange(-17.49, -17.43));
    }
  });
  test('Out of range coordinates are rejected before rendering', () {
    expect(NavigationProgress.decodePolyline('_gjaR?'), isEmpty);
  });
  test('Missing geometry does not invent navigation progress', () {
    final tracker = NavigationProgress(
        NavigationRoute(sessionId: 8, distanceMeters: 10, etaMinutes: 1));
    expect(tracker.locate(point(0, 0)), isNull);
  });
}
