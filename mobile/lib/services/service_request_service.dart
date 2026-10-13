import "dart:convert";
import "../services/api_client.dart";
import "../models/service_request.dart";
import "../models/available_driver.dart";
import "logger_service.dart";

class ServiceRequestService {
  static const _tag = 'ServiceRequestService';

  ServiceRequestService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  /// Liste toutes les demandes de service du client connecte.
  Future<List<ServiceRequest>> list() async {
    logger.info(_tag, 'Chargement liste demandes...');
    final response = await _client.get("/api/services");

    if (response.statusCode < 200 || response.statusCode >= 300) {
      logger.warning(_tag, 'Erreur liste: ${response.statusCode}');
      return [];
    }

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    final data = payload["data"] as List<dynamic>? ?? [];
    logger.info(_tag, '${data.length} demandes chargees');

    return data
        .map((e) => ServiceRequest.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// Recupere le detail d'une demande de service.
  Future<ServiceRequest?> show(int id) async {
    logger.info(_tag, 'Chargement demande #$id...');
    final response = await _client.get("/api/services/$id");

    if (response.statusCode < 200 || response.statusCode >= 300) {
      logger.warning(_tag, 'Erreur show #$id: ${response.statusCode}');
      return null;
    }

    final payload = jsonDecode(response.body) as Map<String, dynamic>;
    final data = payload["data"] as Map<String, dynamic>?;

    if (data == null) return null;
    return ServiceRequest.fromJson(data);
  }

  /// Alias for show() for convenience
  Future<ServiceRequest?> getById(int id) => show(id);

  /// Recupere la liste des vidangeurs disponibles avec leur distance.
  Future<List<AvailableDriver>> getAvailableDrivers({
    required double latitude,
    required double longitude,
  }) async {
    logger.info(_tag, 'Chargement vidangeurs disponibles: lat=$latitude, lng=$longitude');

    try {
      final response = await _client.get(
        "/api/drivers/available?latitude=$latitude&longitude=$longitude",
      );

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur liste vidangeurs: ${response.statusCode}');
        return [];
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload["data"] as List<dynamic>? ?? [];
      logger.info(_tag, '${data.length} vidangeurs disponibles');

      return data
          .map((e) => AvailableDriver.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      logger.error(_tag, 'Exception lors du chargement vidangeurs', e);
      return [];
    }
  }

  /// Estime le prix pour un volume donne.
  Future<Map<String, dynamic>?> estimatePrice({
    required double estimatedVolume,
  }) async {
    logger.info(_tag, 'Estimation prix pour volume=$estimatedVolume m³');

    try {
      final response = await _client.post("/api/drivers/estimate-price", {
        "estimated_volume": estimatedVolume,
      });

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur estimation: ${response.statusCode}');
        return null;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      return payload["data"] as Map<String, dynamic>?;
    } catch (e) {
      logger.error(_tag, 'Exception estimation prix', e);
      return null;
    }
  }

  /// Cree une nouvelle demande de vidange.
  Future<Map<String, dynamic>?> create({
    required String address,
    String? fosseType,
    double? estimatedVolume,
    String? urgencyLevel,
    String? clientNotes,
    int? driverId,
    required String paymentMethod,
    required double latitude,
    required double longitude,
  }) async {
    logger.info(_tag, 'Creation demande: address=$address, volume=$estimatedVolume, payment=$paymentMethod');

    try {
      final response = await _client.post("/api/services", {
        "address": address,
        if (fosseType != null) "fosse_type": fosseType,
        if (estimatedVolume != null) "estimated_volume": estimatedVolume,
        if (urgencyLevel != null) "urgency_level": urgencyLevel,
        if (clientNotes != null) "client_notes": clientNotes,
        if (driverId != null) "driver_id": driverId,
        "payment_method": paymentMethod,
        "latitude": latitude,
        "longitude": longitude,
      });

      logger.debug(_tag, 'Response body: ${response.body}');

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.error(_tag, 'Erreur creation: ${response.statusCode} - ${response.body}');
        return null;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload["data"] as Map<String, dynamic>?;
      logger.info(_tag, 'Creation ${data != null ? "reussie" : "echouee"}');
      return data;
    } catch (e) {
      logger.error(_tag, 'Exception lors de la creation', e);
      return null;
    }
  }

  /// Annule une demande de service.
  Future<bool> cancel(int id) async {
    logger.info(_tag, 'Annulation demande #$id');
    final response = await _client.post("/api/services/$id/cancel", {});

    if (response.statusCode < 200 || response.statusCode >= 300) {
      logger.warning(_tag, 'Erreur annulation: ${response.statusCode}');
      return false;
    }

    return true;
  }

  /// Note une intervention terminee.
  Future<bool> rate(int id, {required int rating, String? comment}) async {
    logger.info(_tag, 'Notation demande #$id: $rating etoiles');
    final response = await _client.post("/api/services/$id/rate", {
      "rating": rating,
      if (comment != null) "comment": comment,
    });

    if (response.statusCode < 200 || response.statusCode >= 300) {
      logger.warning(_tag, 'Erreur notation: ${response.statusCode}');
      return false;
    }

    return true;
  }

  /// Recupere la position du vidangeur en route.
  Future<Map<String, dynamic>?> getDriverPosition(int serviceRequestId) async {
    logger.info(_tag, 'Position vidangeur pour demande #$serviceRequestId');

    try {
      final response = await _client.get("/api/services/$serviceRequestId/driver-position");

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur position: ${response.statusCode}');
        return null;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      return payload["data"] as Map<String, dynamic>?;
    } catch (e) {
      logger.error(_tag, 'Exception position vidangeur', e);
      return null;
    }
  }
}
