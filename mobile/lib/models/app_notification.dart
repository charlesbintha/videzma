class AppNotification {
  final int id;
  final String channel;
  final String title;
  final String body;
  final Map<String, dynamic>? data;
  final String status;
  final bool read;
  final DateTime? sentAt;
  final DateTime? readAt;
  final DateTime? createdAt;

  const AppNotification({
    required this.id,
    required this.channel,
    required this.title,
    required this.body,
    this.data,
    required this.status,
    required this.read,
    this.sentAt,
    this.readAt,
    this.createdAt,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] as int,
      channel: json['channel'] as String,
      title: json['title'] as String,
      body: json['body'] as String,
      data: json['data'] as Map<String, dynamic>?,
      status: json['status'] as String,
      read: json['read'] as bool? ?? false,
      sentAt: _parseDateTime(json['sent_at']),
      readAt: _parseDateTime(json['read_at']),
      createdAt: _parseDateTime(json['created_at']),
    );
  }

  static DateTime? _parseDateTime(dynamic value) {
    if (value == null) return null;
    return DateTime.tryParse(value as String);
  }

  String get channelLabel {
    switch (channel) {
      case 'push':
        return 'Push';
      case 'email':
        return 'Email';
      case 'sms':
        return 'SMS';
      case 'in_app':
        return 'Application';
      default:
        return channel;
    }
  }

  String get channelIcon {
    switch (channel) {
      case 'push':
        return 'notifications';
      case 'email':
        return 'email';
      case 'sms':
        return 'sms';
      case 'in_app':
        return 'phone_android';
      default:
        return 'notifications';
    }
  }

  /// Extrait le type de notification depuis les data (si present).
  String? get notificationType {
    return data?['type'] as String?;
  }

  /// Extrait l'ID de la ressource liee (consultation, appointment, etc).
  int? get relatedId {
    return data?['id'] as int?;
  }
}
