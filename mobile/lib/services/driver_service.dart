import 'dart:convert';
import '../models/service_request.dart';
import 'api_client.dart';
import 'logger_service.dart';

/// Service for driver-specific API operations
class DriverService {
  static const _tag = 'DriverService';
  final ApiClient _client;

  DriverService({ApiClient? client}) : _client = client ?? ApiClient();

  /// Get all service requests assigned to the driver
  Future<List<ServiceRequest>> getAssignedRequests() async {
    try {
      logger.info(_tag, 'Fetching assigned requests...');
      final response = await _client.get('/api/driver/services');

      if (response.statusCode == 200) {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final data = json['data'] as List<dynamic>? ?? [];
        final requests = data
            .map((e) => ServiceRequest.fromJson(e as Map<String, dynamic>))
            .toList();
        logger.info(_tag, 'Loaded ${requests.length} assigned requests');
        return requests;
      } else {
        logger.warning(_tag, 'Failed to load requests: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      logger.error(_tag, 'Error fetching assigned requests', e);
      return [];
    }
  }

  /// Get pending service requests (available to accept)
  Future<List<ServiceRequest>> getPendingRequests() async {
    try {
      logger.info(_tag, 'Fetching pending requests...');
      final response = await _client.get('/api/driver/services/pending');

      if (response.statusCode == 200) {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final data = json['data'] as List<dynamic>? ?? [];
        final requests = data
            .map((e) => ServiceRequest.fromJson(e as Map<String, dynamic>))
            .toList();
        logger.info(_tag, 'Loaded ${requests.length} pending requests');
        return requests;
      } else {
        logger.warning(_tag, 'Failed to load pending requests: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      logger.error(_tag, 'Error fetching pending requests', e);
      return [];
    }
  }

  /// Get a single service request by ID
  Future<ServiceRequest?> getById(int id) async {
    try {
      logger.info(_tag, 'Fetching request #$id...');
      final response = await _client.get('/api/driver/services/$id');

      if (response.statusCode == 200) {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final data = json['data'] as Map<String, dynamic>?;
        if (data != null) {
          return ServiceRequest.fromJson(data);
        }
      }
      return null;
    } catch (e) {
      logger.error(_tag, 'Error fetching request #$id', e);
      return null;
    }
  }

  /// Accept a service request
  Future<DriverActionResult> acceptRequest(int requestId) async {
    try {
      logger.info(_tag, 'Accepting request #$requestId...');
      final response = await _client.post(
        '/api/driver/services/$requestId/accept',
        {},
      );

      if (response.statusCode == 200) {
        logger.info(_tag, 'Request #$requestId accepted');
        return DriverActionResult(success: true, message: 'Demande acceptée');
      } else {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final message = json['message'] as String? ?? 'Erreur';
        logger.warning(_tag, 'Failed to accept: $message');
        return DriverActionResult(success: false, message: message);
      }
    } catch (e) {
      logger.error(_tag, 'Error accepting request', e);
      return DriverActionResult(success: false, message: 'Erreur de connexion');
    }
  }

  /// Reject a service request
  Future<DriverActionResult> rejectRequest(int requestId, {String? reason}) async {
    try {
      logger.info(_tag, 'Rejecting request #$requestId...');
      final response = await _client.post(
        '/api/driver/services/$requestId/reject',
        {'reason': reason},
      );

      if (response.statusCode == 200) {
        logger.info(_tag, 'Request #$requestId rejected');
        return DriverActionResult(success: true, message: 'Demande refusée');
      } else {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final message = json['message'] as String? ?? 'Erreur';
        return DriverActionResult(success: false, message: message);
      }
    } catch (e) {
      logger.error(_tag, 'Error rejecting request', e);
      return DriverActionResult(success: false, message: 'Erreur de connexion');
    }
  }

  /// Start an intervention
  Future<DriverActionResult> startIntervention(int requestId) async {
    try {
      logger.info(_tag, 'Starting intervention for request #$requestId...');
      final response = await _client.post(
        '/api/driver/services/$requestId/start',
        {},
      );

      if (response.statusCode == 200) {
        logger.info(_tag, 'Intervention started for #$requestId');
        return DriverActionResult(success: true, message: 'Intervention démarrée');
      } else {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final message = json['message'] as String? ?? 'Erreur';
        return DriverActionResult(success: false, message: message);
      }
    } catch (e) {
      logger.error(_tag, 'Error starting intervention', e);
      return DriverActionResult(success: false, message: 'Erreur de connexion');
    }
  }

  /// Complete an intervention
  Future<DriverActionResult> completeIntervention(
    int requestId, {
    required double actualVolume,
    String? notes,
    String? photoBefore,
    String? photoAfter,
  }) async {
    try {
      logger.info(_tag, 'Completing intervention for request #$requestId...');
      final response = await _client.post(
        '/api/driver/services/$requestId/complete',
        {
          'actual_volume': actualVolume,
          if (notes != null) 'driver_notes': notes,
          if (photoBefore != null) 'photo_before': photoBefore,
          if (photoAfter != null) 'photo_after': photoAfter,
        },
      );

      if (response.statusCode == 200) {
        logger.info(_tag, 'Intervention completed for #$requestId');
        return DriverActionResult(success: true, message: 'Intervention terminée');
      } else {
        final json = jsonDecode(response.body) as Map<String, dynamic>;
        final message = json['message'] as String? ?? 'Erreur';
        return DriverActionResult(success: false, message: message);
      }
    } catch (e) {
      logger.error(_tag, 'Error completing intervention', e);
      return DriverActionResult(success: false, message: 'Erreur de connexion');
    }
  }

  /// Get driver statistics
  Future<DriverStats> getStats() async {
    try {
      final requests = await getAssignedRequests();

      final pending = requests.where((r) =>
        r.status == 'pending' || r.status == 'assigned'
      ).length;

      final accepted = requests.where((r) => r.status == 'accepted').length;
      final inProgress = requests.where((r) => r.status == 'in_progress').length;
      final completed = requests.where((r) => r.status == 'completed').length;

      return DriverStats(
        totalAssigned: requests.length,
        pending: pending,
        accepted: accepted,
        inProgress: inProgress,
        completed: completed,
      );
    } catch (e) {
      logger.error(_tag, 'Error getting stats', e);
      return DriverStats.empty();
    }
  }
}

/// Result of a driver action (accept, reject, start, complete)
class DriverActionResult {
  final bool success;
  final String message;
  final ServiceRequest? updatedRequest;

  DriverActionResult({
    required this.success,
    required this.message,
    this.updatedRequest,
  });
}

/// Driver statistics
class DriverStats {
  final int totalAssigned;
  final int pending;
  final int accepted;
  final int inProgress;
  final int completed;

  DriverStats({
    required this.totalAssigned,
    required this.pending,
    required this.accepted,
    required this.inProgress,
    required this.completed,
  });

  factory DriverStats.empty() => DriverStats(
    totalAssigned: 0,
    pending: 0,
    accepted: 0,
    inProgress: 0,
    completed: 0,
  );

  int get activeCount => accepted + inProgress;
}
