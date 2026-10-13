import 'dart:async';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:videzma_mobile/models/driver_position.dart';
import 'package:videzma_mobile/services/api_client.dart';
import 'package:videzma_mobile/services/driver_tracking_service.dart';

void main() {
  test('An old or server-stale position cannot announce an imminent arrival',
      () {
    DriverPosition position(int age, {bool fresh = true}) => DriverPosition(
          latitude: 14.7,
          longitude: -17.4,
          timestamp: DateTime.now().millisecondsSinceEpoch ~/ 1000 - age,
          distanceMeters: 20,
          etaMinutes: 0,
          driverName: 'Test',
          serverFresh: fresh,
        );
    expect(position(10).isArriving, isTrue);
    expect(position(180).isArriving, isFalse);
    expect(position(10, fresh: false).isNearby, isFalse);
    expect(position(180).formattedEta, '--');
  });

  test('Polling stops when the API closes tracking', () async {
    final service = DriverTrackingService(
        client: ApiClient(
            client: MockClient(
                (_) async => http.Response('{"tracking":false}', 200))));
    final result = service.positionStream.first;
    service.startTracking(8);
    expect(await result, isNull);
    expect(service.isTracking, isFalse);
    service.dispose();
  });

  test('A response from a previous mission cannot stop a new tracking session',
      () async {
    final oldResponse = Completer<http.Response>();
    final newResponse = Completer<http.Response>();
    final service = DriverTrackingService(
        client: ApiClient(
            client: MockClient((request) => request.url.path.endsWith('/8')
                ? oldResponse.future
                : newResponse.future)));
    final received = <DriverPosition?>[];
    final subscription = service.positionStream.listen(received.add);
    service.startTracking(8);
    service.startTracking(9);
    oldResponse.complete(http.Response('{"tracking":false}', 200));
    await Future<void>.delayed(Duration.zero);
    expect(service.isTracking, isTrue);
    expect(received, isEmpty);
    service.dispose();
    newResponse.complete(http.Response('{"tracking":false}', 200));
    await subscription.cancel();
  });
}
