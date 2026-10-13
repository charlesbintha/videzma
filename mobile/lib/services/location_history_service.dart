import "dart:convert";
import "../models/location_point.dart";
import "../services/api_client.dart";
import "../services/session_store.dart";

class LocationHistoryService {
  LocationHistoryService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  Future<List<LocationPoint>> fetch({int limit = 100}) async {
    final user = SessionStore.user;
    if (user == null) {
      return [];
    }

    final response = await _client.get(
      "/api/clients/${user.id}/locations",
      query: {"limit": limit.toString()},
    );

    if (response.statusCode < 200 || response.statusCode >= 300) {
      return [];
    }

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    final data = payload["data"] as List<dynamic>? ?? [];

    return data
        .map((item) => LocationPoint.fromJson(item as Map<String, dynamic>))
        .toList();
  }
}
