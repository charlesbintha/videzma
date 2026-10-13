import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../models/app_notification.dart";
import "../services/notification_service.dart";
import "service_request_detail_screen.dart";

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final NotificationService _service = NotificationService();

  List<AppNotification> _notifications = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    final notifications = await _service.list();
    setState(() {
      _notifications = notifications;
      _loading = false;
    });
  }

  Future<void> _markAllAsRead() async {
    final success = await _service.markAllAsRead();
    if (success) {
      _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Toutes les notifications marquees comme lues"),
            backgroundColor: AppColors.success,
          ),
        );
      }
    }
  }

  Future<void> _onNotificationTap(AppNotification notification) async {
    // Mark as read
    if (!notification.read) {
      await _service.markAsRead(notification.id);
    }

    // Navigate based on notification type
    if (mounted) {
      final type = notification.notificationType;
      final relatedId = notification.relatedId;

      if ((type == 'service_request' || type == 'intervention') && relatedId != null) {
        await Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => ServiceRequestDetailScreen(requestId: relatedId),
          ),
        );
      }

      _loadData();
    }
  }

  int get _unreadCount => _notifications.where((n) => !n.read).length;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.navy),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          "Notifications",
          style: TextStyle(
            color: AppColors.navy,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        actions: [
          if (_unreadCount > 0)
            TextButton(
              onPressed: _markAllAsRead,
              child: const Text("Tout lire"),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _notifications.isEmpty
                ? _EmptyState()
                : ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemCount: _notifications.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final notification = _notifications[index];
                      return _NotificationCard(
                        notification: notification,
                        onTap: () => _onNotificationTap(notification),
                        onDismiss: () async {
                          await _service.markAsRead(notification.id);
                          _loadData();
                        },
                      );
                    },
                  ),
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final AppNotification notification;
  final VoidCallback onTap;
  final VoidCallback onDismiss;

  const _NotificationCard({
    required this.notification,
    required this.onTap,
    required this.onDismiss,
  });

  @override
  Widget build(BuildContext context) {
    final isUnread = !notification.read;

    return Dismissible(
      key: Key('notification_${notification.id}'),
      direction: DismissDirection.endToStart,
      onDismissed: (_) => onDismiss(),
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: AppColors.success,
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Icon(Icons.check, color: Colors.white),
      ),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isUnread ? Colors.white : AppColors.surface2,
            borderRadius: BorderRadius.circular(16),
            border: isUnread
                ? Border.all(color: AppColors.blue.withOpacity(0.3), width: 2)
                : null,
            boxShadow: isUnread
                ? [
                    BoxShadow(
                      color: AppColors.blue.withOpacity(0.1),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _IconBadge(
                notification: notification,
                isUnread: isUnread,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            notification.title,
                            style: TextStyle(
                              fontWeight:
                                  isUnread ? FontWeight.w700 : FontWeight.w600,
                              fontSize: 15,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (isUnread)
                          Container(
                            height: 8,
                            width: 8,
                            decoration: const BoxDecoration(
                              color: AppColors.blue,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.body,
                      style: TextStyle(
                        color: isUnread ? AppColors.text : AppColors.textMuted,
                        fontSize: 13,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          Icons.access_time,
                          size: 14,
                          color: AppColors.textMuted.withOpacity(0.7),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          _formatDate(notification.sentAt),
                          style: TextStyle(
                            color: AppColors.textMuted.withOpacity(0.7),
                            fontSize: 11,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: AppColors.textMuted.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            notification.channelLabel,
                            style: TextStyle(
                              color: AppColors.textMuted.withOpacity(0.7),
                              fontSize: 10,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDate(DateTime? date) {
    if (date == null) return "";

    final now = DateTime.now();
    final diff = now.difference(date);

    if (diff.inMinutes < 1) {
      return "A l'instant";
    } else if (diff.inMinutes < 60) {
      return "Il y a ${diff.inMinutes} min";
    } else if (diff.inHours < 24) {
      return "Il y a ${diff.inHours} h";
    } else if (diff.inDays < 7) {
      return "Il y a ${diff.inDays} j";
    } else {
      return "${date.day}/${date.month}/${date.year}";
    }
  }
}

class _IconBadge extends StatelessWidget {
  final AppNotification notification;
  final bool isUnread;

  const _IconBadge({
    required this.notification,
    required this.isUnread,
  });

  @override
  Widget build(BuildContext context) {
    IconData icon;
    Color color;

    final type = notification.notificationType;

    switch (type) {
      case 'service_request':
        icon = Icons.local_shipping_outlined;
        color = AppColors.primary;
        break;
      case 'intervention':
        icon = Icons.build_outlined;
        color = AppColors.accent;
        break;
      case 'location':
        icon = Icons.location_on_outlined;
        color = AppColors.success;
        break;
      default:
        icon = Icons.notifications_outlined;
        color = AppColors.primary;
    }

    return Container(
      height: 44,
      width: 44,
      decoration: BoxDecoration(
        color: color.withOpacity(isUnread ? 0.15 : 0.08),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Icon(
        icon,
        color: isUnread ? color : color.withOpacity(0.6),
        size: 22,
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        const SizedBox(height: 100),
        Icon(
          Icons.notifications_none_outlined,
          size: 72,
          color: AppColors.textMuted.withOpacity(0.3),
        ),
        const SizedBox(height: 20),
        const Text(
          "Aucune notification",
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          "Vous n'avez pas encore recu de notification.\nElles apparaitront ici.",
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AppColors.textMuted,
            fontSize: 14,
          ),
        ),
      ],
    );
  }
}
