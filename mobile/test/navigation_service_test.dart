import 'dart:async';
import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:geolocator/geolocator.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:videzma_mobile/services/api_client.dart';
import 'package:videzma_mobile/services/navigation_service.dart';

http.Response response(int id) => http.Response(
    jsonEncode({
      'data': {'session_id': id, 'distance_meters': 1000, 'eta_minutes': 5}
    }),
    200);
Position fix() => Position(
    longitude: -17.4,
    latitude: 14.7,
    timestamp: DateTime.now(),
    accuracy: 5,
    altitude: 0,
    altitudeAccuracy: 0,
    heading: 0,
    headingAccuracy: 0,
    speed: 2,
    speedAccuracy: 0);
void main() {
  test('GPS keeps updating while a server position request is pending',
      () async {
    final source = StreamController<Position>();
    final pending = Completer<http.Response>();
    final service = NavigationService(
        positionSource: () => source.stream,
        client: ApiClient(client: MockClient((request) async {
          if (request.url.path.endsWith('/position')) return pending.future;
          return response(8);
        })));
    await service.startNavigation(
        clientId: 1, serviceRequestId: 8, originLat: 14.7, originLng: -17.4);
    final received = service.positionStream.first;
    final upload = service.updatePositionOnServer(fix());
    source.add(fix());
    expect((await received).latitude, 14.7);
    pending.complete(response(8));
    await upload;
    service.dispose();
    await source.close();
  });
  test('Refresh sends the GPS capture time and cannot restore a stopped route',
      () async {
    final pending = Completer<http.Response>();
    Map<String, dynamic>? payload;
    final service = NavigationService(
        positionSource: () => const Stream.empty(),
        client: ApiClient(client: MockClient((request) async {
          if (request.url.path.endsWith('/refresh')) {
            payload = jsonDecode(request.body);
            return pending.future;
          }
          return response(8);
        })));
    await service.startNavigation(
        clientId: 1, serviceRequestId: 8, originLat: 14.7, originLng: -17.4);
    final position = fix();
    final refresh = service.updateRoute(clientId: 1, position: position);
    await Future<void>.delayed(Duration.zero);
    expect(
        payload!['captured_at'], position.timestamp.toUtc().toIso8601String());
    await service.stopNavigation();
    pending.complete(response(8));
    expect((await refresh).success, isFalse);
    expect(service.currentRoute, isNull);
    service.dispose();
  });
  test('An old rejected upload cannot close a newer mission', () async {
    final pending = Completer<http.Response>();
    final service = NavigationService(
        positionSource: () => const Stream.empty(),
        client: ApiClient(client: MockClient((request) async {
          if (request.url.path.endsWith('/position')) return pending.future;
          return response(
              (jsonDecode(request.body)['service_request_id'] as int?) ?? 8);
        })));
    await service.startNavigation(
        clientId: 1, serviceRequestId: 8, originLat: 14.7, originLng: -17.4);
    final upload = service.updatePositionOnServer(fix());
    await service.startNavigation(
        clientId: 2, serviceRequestId: 9, originLat: 14.7, originLng: -17.4);
    pending.complete(http.Response('{}', 409));
    await upload;
    expect(service.currentRoute!.sessionId, 9);
    expect(service.isNavigating, isTrue);
    service.dispose();
  });
}
