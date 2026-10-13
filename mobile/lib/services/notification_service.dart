import "dart:convert";
import "../services/api_client.dart";
import "../models/app_notification.dart";
import "logger_service.dart";

class NotificationService {
  static const _tag = 'NotificationService';

  NotificationService({ApiClient? client}) : _client = client ?? ApiClient();

  final ApiClient _client;

  /// Recupere la liste des notifications.
  Future<List<AppNotification>> list() async {
    logger.info(_tag, 'Chargement liste notifications...');
    try {
      final response = await _client.get("/api/notifications");

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur liste: ${response.statusCode}');
        return [];
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload["data"] as List<dynamic>? ?? [];
      logger.info(_tag, '${data.length} notifications chargees');

      return data
          .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      logger.error(_tag, 'Exception list()', e);
      return [];
    }
  }

  /// Recupere le nombre de notifications non lues.
  Future<int> getUnreadCount() async {
    logger.info(_tag, 'Chargement compteur notifications...');
    try {
      final response = await _client.get("/api/notifications/unread-count");

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur unread-count: ${response.statusCode}');
        return 0;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final count = payload["count"] as int? ?? 0;
      logger.info(_tag, '$count notifications non lues');
      return count;
    } catch (e) {
      logger.error(_tag, 'Exception getUnreadCount()', e);
      return 0;
    }
  }

  /// Marque une notification comme lue.
  Future<bool> markAsRead(int notificationId) async {
    final response = await _client.post(
      "/api/notifications/$notificationId/read",
      {},
    );

    return response.statusCode >= 200 && response.statusCode < 300;
  }

  /// Marque toutes les notifications comme lues.
  Future<bool> markAllAsRead() async {
    final response = await _client.post("/api/notifications/read-all", {});

    return response.statusCode >= 200 && response.statusCode < 300;
  }
}
