import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:videzma_mobile/services/api_client.dart';
import 'package:videzma_mobile/services/location_service.dart';
import 'package:videzma_mobile/services/location_store.dart';

void main() {
  setUp(LocationStore.clear);
  test('Envoi des coordonnées et de la date GPS, avec confirmation serveur', () async {
    final captured = DateTime.utc(2026, 10, 9, 12);
    final service = LocationService(client: ApiClient(client: MockClient((request) async {
      expect(request.url.path, '/api/locations');
      final body = jsonDecode(request.body);
      expect(body['latitude'], 14.6937);
      expect(body['longitude'], -17.4441);
      expect(body['captured_at'], captured.toIso8601String());
      return http.Response('{"data":{"id":141}}', 201);
    })));
    expect(await service.sendPosition(latitude:14.6937, longitude:-17.4441, capturedAt:captured), isTrue);
    expect(LocationStore.lastLocationId, 141);
    expect(service.lastError, isNull);
  });
  test('Un refus serveur ne peut pas être présenté comme un partage réussi', () async {
    final service = LocationService(client: ApiClient(client: MockClient((_) async => http.Response('{}', 422))));
    expect(await service.sendPosition(latitude:14.7, longitude:-17.4), isFalse);
    expect(service.lastError, isNotNull);
    expect(LocationStore.lastLocationId, isNull);
  });
  test('Une réponse sans identifiant et une panne réseau sont des échecs explicites', () async {
    for (final client in [
      MockClient((_) async => http.Response('{"data":{}}', 201)),
      MockClient((_) async => throw http.ClientException('offline')),
    ]) {
      final service = LocationService(client: ApiClient(client:client));
      expect(await service.sendPosition(latitude:14.7, longitude:-17.4), isFalse);
      expect(service.lastError, isNotNull);
    }
  });
}
